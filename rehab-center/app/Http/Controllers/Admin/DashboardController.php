<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Обробка навігації по тижнях
        $weekOffset = $request->get('week', 0);
        if ($request->has('week') && $request->week === 'previous') {
            $weekOffset = $request->session()->get('week_offset', 0) - 1;
        } elseif ($request->has('week') && $request->week === 'next') {
            $weekOffset = $request->session()->get('week_offset', 0) + 1;
        } else {
            $weekOffset = (int) $weekOffset;
        }
        $request->session()->put('week_offset', $weekOffset);

        $stats = $this->getStats($user);
        $calendar = $this->getCalendarData($user, $weekOffset);

        return view('admin.dashboard', compact('calendar', 'stats'));
    }

    private function getStats($user)
    {
        $baseQuery = $user->isAdmin() 
            ? Appointment::query()
            : Appointment::where('master_id', $user->id);

        return [
            'today' => (clone $baseQuery)->whereDate('appointment_date', today())->count(),
            'week' => (clone $baseQuery)->whereBetween('appointment_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'month' => (clone $baseQuery)->whereBetween('appointment_date', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
            'upcoming' => (clone $baseQuery)->where('appointment_date', '>=', today())->count(),
        ];
    }

    private function getCalendarData($user, $weekOffset = 0)
    {
        // Отримуємо потрібний тиждень
        $startDate = now()->addWeeks($weekOffset)->startOfWeek();
        $endDate = now()->addWeeks($weekOffset)->endOfWeek();

        // Отримуємо всіх активних майстрів
        $mastersQuery = User::where('role', 'master')
                           ->where('is_active', true);
        
        if ($user->isMaster()) {
            $mastersQuery->where('id', $user->id);
        }
        
        $masters = $mastersQuery->orderBy('name')->get();

        // Отримуємо всі записи на цей тиждень
        $appointmentsQuery = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                           ->with(['client', 'service', 'master'])
                           ->where('status', 'scheduled');

        if ($user->isMaster()) {
            $appointmentsQuery->where('master_id', $user->id);
        }

        $appointments = $appointmentsQuery->orderBy('appointment_date')
                                         ->orderBy('appointment_time')
                                         ->get();

        // Групуємо записи по майстрах, датах і часу
        $scheduleByMaster = [];
        foreach ($masters as $master) {
            $scheduleByMaster[$master->id] = [
                'master' => $master,
                'appointments_by_date_time' => []
            ];
        }

        foreach ($appointments as $appointment) {
            $dateKey = $appointment->appointment_date->format('Y-m-d');
            $timeKey = substr($appointment->appointment_time, 0, 5);
            $key = $dateKey . '_' . $timeKey;
            
            $scheduleByMaster[$appointment->master_id]['appointments_by_date_time'][$key] = [
                'id' => $appointment->id,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'duration' => (int) $appointment->duration,
                'client_name' => $appointment->client->name,
                'service_name' => $appointment->service->name,
                'price' => $appointment->price,
                'status' => $appointment->status,
            ];
        }

        // Визначаємо мінімальний і максимальний час роботи
        $minTime = '09:00';
        $maxTime = '18:00';
        
        if ($appointments->count() > 0) {
            $times = $appointments->pluck('appointment_time')->map(function($time) {
                return substr($time, 0, 5);
            });
            $minTime = $times->min();
            $maxTime = $times->max();
            
            // Додаємо буфер
            $minTimeStamp = strtotime($minTime) - 3600;
            $maxTimeStamp = strtotime($maxTime) + 3600;
            
            // Округлюємо до цілої години
            $minTime = date('H:00', $minTimeStamp);
            $maxTime = date('H:00', $maxTimeStamp);
        }

        // Генеруємо слоти часу (кожну годину)
        $timeSlots = [];
        $current = strtotime($minTime);
        $end = strtotime($maxTime);
        
        while ($current <= $end) {
            $timeSlots[] = date('H:i', $current);
            $current += 3600; // +1 година
        }

        // Генеруємо дати тижня
        $weekDates = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $weekDates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        return [
            'masters' => $masters,
            'scheduleByMaster' => $scheduleByMaster,
            'timeSlots' => $timeSlots,
            'weekDates' => $weekDates,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function getStatusColor($status)
    {
        return [
            'scheduled' => '#10B981',
            'completed' => '#3B82F6',
            'cancelled' => '#EF4444',
        ][$status] ?? '#6B7280';
    }
}