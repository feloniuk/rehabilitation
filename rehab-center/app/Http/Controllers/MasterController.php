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

        // ИСПРАВЛЕНИЕ: приводим duration к integer
        $duration = (int) $masterService->getDuration();
        
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
        
        // ИСПРАВЛЕНИЕ: явно создаем Carbon объекты и используем integer для duration
        $current = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        
        // Убедимся что duration это integer
        $durationInt = (int) $duration;

        while ($current->copy()->addMinutes($durationInt)->lte($end)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($durationInt);

            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                $appointmentEnd = $appointmentStart->copy()->addMinutes((int) $appointment->duration);

                if ($slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = $slotStart->format('H:i');
            }
            
            // ИСПРАВЛЕНИЕ: используем integer
            $current->addMinutes($durationInt);
        }

        return $slots;
    }
}