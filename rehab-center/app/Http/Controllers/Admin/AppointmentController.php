<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['client', 'master', 'service']);

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтрация по мастеру
        if ($request->filled('master_id')) {
            $query->where('master_id', $request->master_id);
        }

        // Фильтрация по услуге
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Фильтрация по дате
        if ($request->filled('date_from')) {
            $query->where('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('appointment_date', '<=', $request->date_to);
        }

        // Фильтрация по клиенту
        if ($request->filled('client_name')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->client_name . '%')
                  ->orWhere('phone', 'like', '%' . $request->client_name . '%')
                  ->orWhere('email', 'like', '%' . $request->client_name . '%');
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
                             ->orderBy('appointment_time', 'desc')
                             ->paginate(20);

        // Данные для фильтров
        $masters = User::where('role', 'master')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $statuses = [
            'scheduled' => 'Заплановано',
            'completed' => 'Завершено',
            'cancelled' => 'Скасовано'
        ];

        return view('admin.appointments.index', compact('appointments', 'masters', 'services', 'statuses'));
    }

    public function show($id)
    {
        $appointment = Appointment::with(['client', 'master', 'service'])->findOrFail($id);
        
        return response()->json([
            'id' => $appointment->id,
            'client' => [
                'name' => $appointment->client->name,
                'phone' => $appointment->client->phone,
                'email' => $appointment->client->email,
            ],
            'master' => [
                'name' => $appointment->master->name,
                'phone' => $appointment->master->phone,
            ],
            'service' => [
                'name' => $appointment->service->name,
                'duration' => $appointment->duration,
            ],
            'appointment_date' => $appointment->appointment_date->format('d.m.Y'),
            'appointment_time' => substr($appointment->appointment_time, 0, 5),
            'price' => number_format($appointment->price, 0),
            'status' => $appointment->status,
            'status_text' => $this->getStatusText($appointment->status),
            'notes' => $appointment->notes,
            'created_at' => $appointment->created_at->format('d.m.Y H:i'),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled'
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Статус запису оновлено',
            'status' => $appointment->status,
            'status_text' => $this->getStatusText($appointment->status)
        ]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return redirect()->route('admin.appointments.index')
                        ->with('success', 'Запис видалено');
    }

    private function getStatusText($status)
    {
        return [
            'scheduled' => 'Заплановано',
            'completed' => 'Завершено',
            'cancelled' => 'Скасовано'
        ][$status] ?? $status;
    }
}