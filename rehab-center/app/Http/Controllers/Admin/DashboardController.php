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
        
        $period = $request->get('period', 'week');
        
        $query = $user->isAdmin() 
            ? Appointment::with(['client', 'master', 'service'])
            : Appointment::with(['client', 'service'])->where('master_id', $user->id);
        
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
                $query->whereBetween('appointment_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                $periodTitle = 'Цей тиждень';
        }

        $appointments = $query->orderBy('appointment_date')
                             ->orderBy('appointment_time')
                             ->paginate(20);

        $stats = $this->getStats($user);
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
                           ->with(['client', 'service', 'master'])
                           ->where('status', 'scheduled');

        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointments = $query->get();
        
        // Групуємо записи по даті та часу для відображення накладення
        $groupedAppointments = $appointments->groupBy(function($appointment) {
            return $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time;
        });

        $events = [];
        
        foreach ($groupedAppointments as $datetime => $appointmentGroup) {
            $count = $appointmentGroup->count();
            
            if ($count > 1) {
                // Якщо кілька записів на один час - створюємо групову подію
                $firstAppointment = $appointmentGroup->first();
                $duration = (int) $firstAppointment->duration;
                
                $mastersList = $appointmentGroup->map(function($apt) {
                    return $apt->master->name . ': ' . $apt->client->name;
                })->join("\n");
                
                $events[] = [
                    'title' => "📋 {$count} записів",
                    'start' => $firstAppointment->getStartDateTime()->toISOString(),
                    'end' => $firstAppointment->getStartDateTime()->addMinutes($duration)->toISOString(),
                    'color' => '#F59E0B', // Orange для групових записів
                    'extendedProps' => [
                        'isGroup' => true,
                        'count' => $count,
                        'appointments' => $appointmentGroup->map(function($apt) {
                            return [
                                'id' => $apt->id,
                                'master' => $apt->master->name,
                                'client' => $apt->client->name,
                                'service' => $apt->service->name,
                            ];
                        })->toArray(),
                        'description' => $mastersList,
                    ],
                ];
            } else {
                // Одиночний запис
                $appointment = $appointmentGroup->first();
                $duration = (int) $appointment->duration;
                
                $events[] = [
                    'title' => $appointment->service->name . ' - ' . $appointment->client->name,
                    'start' => $appointment->getStartDateTime()->toISOString(),
                    'end' => $appointment->getStartDateTime()->addMinutes($duration)->toISOString(),
                    'color' => $this->getStatusColor($appointment->status),
                    'extendedProps' => [
                        'isGroup' => false,
                        'appointment_id' => $appointment->id,
                        'master' => $appointment->master->name,
                    ],
                ];
            }
        }

        return $events;
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