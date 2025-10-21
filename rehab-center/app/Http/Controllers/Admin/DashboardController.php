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
        
        // Визначаємо період для відображення
        $period = $request->get('period', 'week'); // week, month, all
        
        // Базовий запит
        $query = $user->isAdmin() 
            ? Appointment::with(['client', 'master', 'service'])
            : Appointment::with(['client', 'service'])->where('master_id', $user->id);
        
        // Фільтрація по періоду
        switch ($period) {
            case 'today':
                $query->whereDate('appointment_date', today());
                $periodTitle = 'Сьогодні';
                break;
            case 'week':
                $query->whereBetween('appointment_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                $periodTitle = 'Цей тиждень';
                break;
            case 'month':
                $query->whereBetween('appointment_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]);
                $periodTitle = 'Цей місяць';
                break;
            case 'upcoming':
                $query->where('appointment_date', '>=', today());
                $periodTitle = 'Майбутні';
                break;
            default:
                // По замовчуванню - тиждень
                $query->whereBetween('appointment_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                $periodTitle = 'Цей тиждень';
        }

        $appointments = $query->orderBy('appointment_date')
                             ->orderBy('appointment_time')
                             ->paginate(20);

        // Статистика для відображення
        $stats = $this->getStats($user);
        
        // Календар для поточного місяця
        $calendar = $this->getCalendarData($user);

        return view('admin.dashboard', compact('appointments', 'calendar', 'periodTitle', 'period', 'stats'));
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

    private function getCalendarData($user)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $query = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                           ->with(['client', 'service', 'master']);

        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        return $query->get()->map(function ($appointment) {
            // ИСПРАВЛЕНИЕ: явно приводим duration к integer
            $duration = (int) $appointment->duration;
            
            return [
                'title' => $appointment->service->name . ' - ' . $appointment->client->name,
                'start' => $appointment->getStartDateTime()->toISOString(),
                'end' => $appointment->getStartDateTime()->addMinutes($duration)->toISOString(),
                'color' => $this->getStatusColor($appointment->status),
                'appointment_id' => $appointment->id,
            ];
        });
    }

    private function getStatusColor($status)
    {
        return [
            'scheduled' => '#10B981', // green
            'completed' => '#3B82F6', // blue
            'cancelled' => '#EF4444', // red
        ][$status] ?? '#6B7280'; // gray
    }
}