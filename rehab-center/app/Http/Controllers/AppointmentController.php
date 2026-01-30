<?php

namespace App\Http\Controllers;

use App\Helpers\PhoneHelper;
use App\Models\Appointment;
use App\Models\MasterService;
use App\Models\Service;
use App\Models\User;
use App\Services\MasterTelegramBotNotificationService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function create(Request $request)
    {
        $masterId = $request->get('master_id');
        $serviceId = $request->get('service_id');

        $master = User::where('role', 'master')->findOrFail($masterId);
        $service = Service::findOrFail($serviceId);
        $masterService = MasterService::where('master_id', $masterId)
            ->where('service_id', $serviceId)
            ->firstOrFail();

        return view('appointments.create', compact('master', 'service', 'masterService'));
    }

    public function store(\App\Http\Requests\StoreAppointmentRequest $request, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $validated = $request->validated();

        // Нормалізуємо телефон та шукаємо/створюємо клієнта
        $normalizedPhone = PhoneHelper::normalize($validated['phone']);

        $client = User::firstOrCreate(
            ['phone' => $normalizedPhone],
            [
                'name' => $validated['name'],
                'role' => 'client',
                'password' => bcrypt(str()->random(12)),
            ]
        );

        $masterService = MasterService::where('master_id', $validated['master_id'])
            ->where('service_id', $validated['service_id'])
            ->firstOrFail();

        // ИСПРАВЛЕНИЕ: явно приводим к integer
        $duration = (int) $masterService->getDuration();
        $price = (float) $masterService->price;

        $appointment = Appointment::create([
            'client_id' => $client->id,
            'master_id' => $validated['master_id'],
            'service_id' => $validated['service_id'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'duration' => $duration,
            'price' => $price,
            'notes' => $request->input('notes'),
        ]);

        $masterTelegramBotService->sendMasterNotification($appointment);

        return redirect()->route('appointment.success')->with('appointment_id', $appointment->id);
    }

    public function success()
    {
        $appointmentId = session('appointment_id');
        $appointment = $appointmentId ? Appointment::with(['master', 'service'])->find($appointmentId) : null;

        return view('appointments.success', compact('appointment'));
    }

    public function cancel($id, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $appointment = Appointment::findOrFail($id);

        if (! $appointment->canBeCancelled()) {
            return back()->with('error', 'Неможливо скасувати запис менше ніж за 24 години до прийому');
        }

        $appointment->update(['status' => 'cancelled']);

        $masterTelegramBotService->sendCancellationNotification($appointment);

        return back()->with('success', 'Запис успішно скасовано');
    }
}
