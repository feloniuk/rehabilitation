<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $appointments = Appointment::with(['client', 'master', 'service'])
                                      ->where('appointment_date', '>=', today())
                                      ->orderBy('appointment_date')
                                      ->orderBy('appointment_time')
                                      ->paginate(20);
        } else {
            $appointments = Appointment::with(['client', 'service'])
                                      ->where('master_id', $user->id)
                                      ->where('appointment_date', '>=', today())
                                      ->orderBy('appointment_date')
                                      ->orderBy('appointment_time')
                                      ->paginate(20);
        }

        $calendar = $this->getCalendarData($user);

        return view('admin.dashboard', compact('appointments', 'calendar'));
    }

    private function getCalendarData($user)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $query = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                           ->where('status', 'scheduled')
                           ->with(['client', 'service']);

        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        return $query->get()->map(function ($appointment) {
            return [
                'title' => $appointment->service->name . ' - ' . $appointment->client->name,
                'start' => $appointment->getStartDateTime()->toISOString(),
                'end' => $appointment->getEndDateTime()->toISOString(),
                'color' => '#3B82F6',
            ];
        });
    }
}