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
    public function create($tenant, Request $request)
    {
        $masterId = $request->get('master_id');
        $serviceId = $request->get('service_id');

        $master = User::masters()->ofTenant()->findOrFail($masterId);
        // GlobalScope from BelongsToTenant trait filters by current tenant automatically
        $service = Service::findOrFail($serviceId);
        $masterService = MasterService::where('master_id', $masterId)
            ->where('service_id', $serviceId)
            ->firstOrFail();

        return view('appointments.create', compact('master', 'service', 'masterService'));
    }

    public function store($tenant, Request $request, MasterTelegramBotNotificationService $masterTelegramBotService)
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
            ['phone' => $normalizedPhone, 'tenant_id' => app('currentTenant')->id],
            [
                'name' => $request->name,
                'role' => 'client',
                'password' => bcrypt(str()->random(12)),
                'tenant_id' => app('currentTenant')->id,
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

        return redirect()->route('tenant.appointment.success', ['tenant' => app('currentTenant')->slug])->with('appointment_id', $appointment->id);
    }

    public function success($tenant)
    {
        $appointmentId = session('appointment_id');
        $appointment = $appointmentId ? Appointment::with(['master', 'service'])->find($appointmentId) : null;

        return view('appointments.success', compact('appointment'));
    }

    public function cancel($tenant, $appointment)
    {
        $appointmentModel = Appointment::findOrFail($appointment);

        if (! $appointmentModel->canBeCancelled()) {
            return back()->with('error', 'Неможливо скасувати запис менше ніж за 24 години до прийому');
        }

        $appointmentModel->update(['status' => 'cancelled']);

        return back()->with('success', 'Запис успішно скасовано');
    }
}
