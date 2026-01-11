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

    public function store(Request $request, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'master_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        // Нормалізуємо телефон та шукаємо/створюємо клієнта
        $normalizedPhone = PhoneHelper::normalize($request->phone);

        $client = User::firstOrCreate(
            ['phone' => $normalizedPhone],
            [
                'name' => $request->name,
                'role' => 'client',
                'password' => bcrypt(str()->random(12)),
            ]
        );

        $masterService = MasterService::where('master_id', $request->master_id)
            ->where('service_id', $request->service_id)
            ->firstOrFail();

        // ИСПРАВЛЕНИЕ: явно приводим к integer
        $duration = (int) $masterService->getDuration();
        $price = (float) $masterService->price;

        $appointment = Appointment::create([
            'client_id' => $client->id,
            'master_id' => $request->master_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration' => $duration,
            'price' => $price,
            'notes' => $request->notes,
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
