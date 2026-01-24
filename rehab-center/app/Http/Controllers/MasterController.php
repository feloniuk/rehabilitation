<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
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

        // Перевіряємо чи дата не заблокована
        if ($master->isBlockedOn($requestDate)) {
            return response()->json([
                'slots' => [],
                'blocked' => true,
                'message' => 'Майстер недоступний в цей день',
            ]);
        }

        $dayName = strtolower($requestDate->format('l'));

        if (! $master->isWorkingOnDay($dayName)) {
            return response()->json([]);
        }

        $workingHours = $master->getWorkingHours($dayName);
        $masterService = $master->masterServices()
            ->where('service_id', $serviceId)
            ->first();

        if (! $masterService) {
            return response()->json([]);
        }

        // ИСПРАВЛЕНИЕ: приводим duration к integer
        $duration = (int) $masterService->getDuration();

        $existingAppointments = Appointment::where('master_id', $id)
            ->where('appointment_date', $date)
            ->where('status', 'scheduled')
            ->get();

        // Определяем минимальное время для сегодняшней даты
        $minTime = null;
        if ($requestDate->isToday()) {
            $minTime = Carbon::now()->format('H:i');
        }

        $slots = $this->generateAvailableSlots(
            $workingHours['start'],
            $workingHours['end'],
            $duration,
            $existingAppointments,
            $minTime
        );

        return response()->json($slots);
    }

    private function generateAvailableSlots($startTime, $endTime, $duration, $existingAppointments, $minTime = null)
    {
        $slots = [];

        // ИСПРАВЛЕНИЕ: явно создаем Carbon объекты и используем integer для duration
        $current = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        // Минимальное время (для сегодняшней даты - текущее время)
        $minTimeCarbon = $minTime ? Carbon::createFromFormat('H:i', $minTime) : null;

        // Убедимся что duration это integer
        $durationInt = (int) $duration;

        while ($current->copy()->addMinutes($durationInt)->lte($end)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($durationInt);

            // Пропускаем слоты, которые уже прошли (для сегодняшней даты)
            if ($minTimeCarbon && $slotStart->lte($minTimeCarbon)) {
                $current->addMinutes($durationInt);

                continue;
            }

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

    /**
     * Отримання першого вільного слоту та робочого часу мастра
     */
    public function getFirstAvailableSlot($id, $date, $serviceId)
    {
        $master = User::where('role', 'master')->find($id);

        if (! $master) {
            return response()->json([
                'success' => false,
                'message' => 'Майстра не знайдено',
            ], 404);
        }

        $requestDate = Carbon::parse($date);

        // Перевіряємо чи дата не заблокована
        if ($master->isBlockedOn($requestDate)) {
            return response()->json([
                'success' => true,
                'is_working_day' => false,
                'blocked' => true,
                'message' => 'Майстер недоступний в цей день',
                'working_hours' => null,
                'first_available_slot' => null,
            ]);
        }

        $dayName = strtolower($requestDate->format('l'));

        // Отримуємо робочий час мастра
        $workingHours = null;
        $isWorkingDay = $master->isWorkingOnDay($dayName);

        if ($isWorkingDay) {
            $workingHours = $master->getWorkingHours($dayName);
        }

        // Якщо мастер не працює в цей день
        if (! $isWorkingDay || ! $workingHours) {
            return response()->json([
                'success' => true,
                'is_working_day' => false,
                'working_hours' => null,
                'first_available_slot' => null,
                'message' => 'Майстер не працює в цей день',
            ]);
        }

        // Отримуємо послугу мастра
        $masterService = $master->masterServices()
            ->where('service_id', $serviceId)
            ->first();

        if (! $masterService) {
            return response()->json([
                'success' => true,
                'is_working_day' => true,
                'working_hours' => $workingHours,
                'first_available_slot' => $workingHours['start'],
                'duration' => 60,
            ]);
        }

        $duration = (int) $masterService->getDuration();

        // Отримуємо існуючі записи
        $existingAppointments = Appointment::where('master_id', $id)
            ->where('appointment_date', $date)
            ->where('status', 'scheduled')
            ->get();

        // Мінімальний час для сьогодення
        $minTime = null;
        if ($requestDate->isToday()) {
            $minTime = Carbon::now()->format('H:i');
        }

        // Генеруємо слоти
        $slots = $this->generateAvailableSlots(
            $workingHours['start'],
            $workingHours['end'],
            $duration,
            $existingAppointments,
            $minTime
        );

        return response()->json([
            'success' => true,
            'is_working_day' => true,
            'working_hours' => $workingHours,
            'first_available_slot' => ! empty($slots) ? $slots[0] : null,
            'available_slots_count' => count($slots),
            'duration' => $duration,
        ]);
    }
}
