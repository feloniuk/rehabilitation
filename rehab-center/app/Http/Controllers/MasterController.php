<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class MasterController extends Controller
{
    public function show($id)
    {
        $master = User::where('role', 'master')
                     ->where('is_active', true)
                     ->with('masterServices.service')
                     ->findOrFail($id);

        return view('masters.show', compact('master'));
    }

    public function getAvailableSlots($id, $date, $serviceId)
    {
        $master = User::where('role', 'master')->findOrFail($id);
        $requestDate = Carbon::parse($date);
        $dayName = strtolower($requestDate->format('l'));

        if (!$master->isWorkingOnDay($dayName)) {
            return response()->json([]);
        }

        $workingHours = $master->getWorkingHours($dayName);
        $masterService = $master->masterServices()
                               ->where('service_id', $serviceId)
                               ->first();

        if (!$masterService) {
            return response()->json([]);
        }

        $duration = $masterService->getDuration();
        $existingAppointments = Appointment::where('master_id', $id)
                                          ->where('appointment_date', $date)
                                          ->where('status', 'scheduled')
                                          ->get();

        $slots = $this->generateAvailableSlots(
            $workingHours['start'],
            $workingHours['end'],
            $duration,
            $existingAppointments
        );

        return response()->json($slots);
    }

    private function generateAvailableSlots($startTime, $endTime, $duration, $existingAppointments)
    {
        $slots = [];
        $current = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        while ($current->addMinutes($duration)->lte($end)) {
            $slotStart = $current->copy()->subMinutes($duration);
            $slotEnd = $current->copy();

            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

                if ($slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = $slotStart->format('H:i');
            }
        }

        return $slots;
    }
}
