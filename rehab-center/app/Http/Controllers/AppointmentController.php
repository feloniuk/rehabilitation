<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Models\MasterService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'master_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        // Создаем или находим клиента
        $client = User::updateOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'role' => 'client',
                'password' => bcrypt(str()->random(12))
            ]
        );

        $masterService = MasterService::where('master_id', $request->master_id)
                                     ->where('service_id', $request->service_id)
                                     ->firstOrFail();

        $appointment = Appointment::create([
            'client_id' => $client->id,
            'master_id' => $request->master_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration' => $masterService->getDuration(),
            'price' => $masterService->price,
            'notes' => $request->notes,
        ]);

        return redirect()->route('appointment.success')->with('appointment_id', $appointment->id);
    }

    public function success()
    {
        $appointmentId = session('appointment_id');
        $appointment = $appointmentId ? Appointment::with(['master', 'service'])->find($appointmentId) : null;

        return view('appointments.success', compact('appointment'));
    }

    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);

        if (!$appointment->canBeCancelled()) {
            return back()->with('error', 'Неможливо скасувати запис менше ніж за 24 години до прийому');
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Запис успішно скасовано');
    }
}