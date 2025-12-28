<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Базовий запит з фільтрацією по ролі
        $query = Appointment::with(['client', 'master', 'service']);

        // КРИТИЧНО: Якщо майстер - показуємо тільки його записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        // Фільтрація по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фільтрація по мастеру (тільки для адміна)
        if ($request->filled('master_id') && $user->isAdmin()) {
            $query->where('master_id', $request->master_id);
        }

        // Фільтрація по послузі
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Фільтрація по даті
        if ($request->filled('date_from')) {
            $query->where('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('appointment_date', '<=', $request->date_to);
        }

        // Фільтрація по клієнту
        if ($request->filled('client_name')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->client_name.'%')
                    ->orWhere('phone', 'like', '%'.$request->client_name.'%')
                    ->orWhere('email', 'like', '%'.$request->client_name.'%');
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        // Дані для фільтрів
        $masters = $user->isAdmin()
            ? User::where('role', 'master')->where('is_active', true)->get()
            : collect(); // Майстру не потрібен список майстрів

        $services = Service::where('is_active', true)->get();
        $statuses = [
            'scheduled' => 'Заплановано',
            'completed' => 'Завершено',
            'cancelled' => 'Скасовано',
        ];

        return view('admin.appointments.index', compact('appointments', 'masters', 'services', 'statuses'));
    }

    public function show($id)
    {
        $user = auth()->user();

        $query = Appointment::with(['client', 'master', 'service']);

        // Майстер може бачити тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);

        return response()->json([
            'id' => $appointment->id,
            'client' => [
                'name' => $appointment->client->name,
                'phone' => $appointment->client->phone,
                'email' => $appointment->client->email,
                'description' => $appointment->client->description,
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
            'telegram_notification_sent' => $appointment->telegram_notification_sent,
            'created_at' => $appointment->created_at->format('d.m.Y H:i'),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();

        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $query = Appointment::query();

        // Майстер може змінювати тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);
        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Статус запису оновлено',
            'status' => $appointment->status,
            'status_text' => $this->getStatusText($appointment->status),
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $query = Appointment::query();

        // Майстер може видаляти тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);
        $appointment->delete();

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Запис видалено');
    }

    private function getStatusText($status)
    {
        return [
            'scheduled' => 'Заплановано',
            'completed' => 'Завершено',
            'cancelled' => 'Скасовано',
        ][$status] ?? $status;
    }
}
