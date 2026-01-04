<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
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
            ->paginate(20)
            ->withQueryString();

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
            'is_confirmed' => $appointment->is_confirmed,
            'created_at' => $appointment->created_at->format('d.m.Y H:i'),
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();

        $query = Appointment::with(['client', 'master', 'service']);

        // Майстер може редагувати тільки свої записи, адмін - будь-які
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);
        $masters = User::where('role', 'master')
            ->where('is_active', true)
            ->get();

        $services = Service::where('is_active', true)->get();

        return view('admin.appointments.edit', compact('appointment', 'masters', 'services'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $query = Appointment::query();

        // Майстер може редагувати тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);

        $rules = [
            'master_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled',
            'allow_overlap' => 'nullable|boolean',
        ];

        $validated = $request->validate($rules);

        // Час уже в правильному форматі HH:mm з JavaScript
        $appointmentTime = $request->appointment_time;

        // Перевірка що час в майбутньому (тільки якщо статус scheduled)
        if ($request->status === 'scheduled') {
            $appointmentDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $request->appointment_date.' '.$appointmentTime
            );

            // Дозволяємо редагувати записи які були в майбутньому, але якщо змінити на прошедший час - не дозволяємо
            if ($appointmentDateTime->isPast()) {
                return back()->withErrors([
                    'appointment_date' => 'Неможливо встановити час в минулому. Виберіть час в майбутньому або змініть статус на "Завершено".',
                ])->withInput();
            }
        }

        // Перевірка на конфлікт часу (крім поточної записи)
        if (! $request->boolean('allow_overlap') && (
            $appointment->appointment_date->format('Y-m-d') !== $request->appointment_date ||
            substr($appointment->appointment_time, 0, 5) !== $appointmentTime ||
            $appointment->master_id != $request->master_id
        )) {
            $conflict = $this->checkTimeConflict(
                $request->master_id,
                $request->appointment_date,
                $appointmentTime,
                $request->duration,
                $id
            );

            if ($conflict) {
                return back()->withErrors([
                    'appointment_time' => 'На цей час вже є запис. Увімкніть "Дозволити нахлест" якщо потрібно.',
                ])->withInput();
            }
        }

        // Оновлення запису
        $appointment->update([
            'master_id' => $request->master_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $appointmentTime.':00',  // Додаємо секунди
            'duration' => $request->duration,
            'price' => $request->price,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Запис успішно оновлено');
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

    public function toggleConfirm($id)
    {
        $user = auth()->user();

        $query = Appointment::query();

        // Майстер може змінювати тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);
        $appointment->is_confirmed = ! $appointment->is_confirmed;
        $appointment->save();

        return response()->json([
            'success' => true,
            'is_confirmed' => $appointment->is_confirmed,
            'message' => $appointment->is_confirmed
                ? 'Запис підтверджено'
                : 'Підтвердження знято',
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

        return back()->with('success', 'Запис видалено');
    }

    private function getStatusText($status)
    {
        return [
            'scheduled' => 'Заплановано',
            'completed' => 'Завершено',
            'cancelled' => 'Скасовано',
        ][$status] ?? $status;
    }

    private function checkTimeConflict(
        int $masterId,
        string $date,
        string $time,
        int $duration,
        ?int $excludeAppointmentId = null
    ): bool {
        $dateOnly = Carbon::parse($date)->format('Y-m-d');
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $dateOnly.' '.$time);
        $endTime = $startTime->copy()->addMinutes($duration);

        $query = Appointment::where('master_id', $masterId)
            ->whereDate('appointment_date', $dateOnly)
            ->where('status', 'scheduled');

        // Виключаємо поточну запис при редагуванні
        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        $existingAppointments = $query->get();

        foreach ($existingAppointments as $appointment) {
            $existingStart = $appointment->getStartDateTime();
            $existingEnd = $appointment->getEndDateTime();

            if ($startTime->lt($existingEnd) && $endTime->gt($existingStart)) {
                return true;
            }
        }

        return false;
    }
}
