<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MasterService;
use App\Models\Service;
use App\Models\User;
use App\Services\MasterTelegramBotNotificationService;
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
                'id' => $appointment->client->id,
                'name' => $appointment->client->name,
                'phone' => $appointment->client->phone,
                'email' => $appointment->client->email,
                'description' => $appointment->client->description,
            ],
            'master' => [
                'id' => $appointment->master->id,
                'name' => $appointment->master->name,
                'phone' => $appointment->master->phone,
            ],
            'service' => [
                'id' => $appointment->service->id,
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
            // Плоські поля для зручності JavaScript функцій
            'client_id' => $appointment->client->id,
            'client_name' => $appointment->client->name,
            'client_phone' => $appointment->client->phone,
            'master_id' => $appointment->master->id,
            'master_name' => $appointment->master->name,
            'service_id' => $appointment->service->id,
            'service_name' => $appointment->service->name,
            'appointment_date_raw' => $appointment->appointment_date->format('Y-m-d'),
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

    public function update(Request $request, $id, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $user = auth()->user();

        $query = Appointment::query();

        // Майстер може редагувати тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);
        $oldStatus = $appointment->status;

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

        // Відправляємо повідомлення майстру якщо статус змінився на "скасовано"
        if ($oldStatus !== 'cancelled' && $request->status === 'cancelled') {
            $masterTelegramBotService->sendCancellationNotification($appointment);
        }

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Запис успішно оновлено');
    }

    public function updateStatus(Request $request, $id, MasterTelegramBotNotificationService $masterTelegramBotService)
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
        $oldStatus = $appointment->status;

        $appointment->update(['status' => $request->status]);

        // Відправляємо повідомлення майстру якщо статус змінився на "скасовано"
        if ($oldStatus !== 'cancelled' && $request->status === 'cancelled') {
            $masterTelegramBotService->sendCancellationNotification($appointment);
        }

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

    public function destroy($id, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $user = auth()->user();

        $query = Appointment::with(['client', 'master', 'service']);

        // Майстер може видаляти тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);

        // Відправляємо повідомлення майстру про видалення запису (якщо він був запланований)
        if ($appointment->status === 'scheduled') {
            $masterTelegramBotService->sendCancellationNotification($appointment);
        }

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

    /**
     * Створення повторного запису на основі існуючого
     */
    public function repeat(Request $request, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $request->validate([
            'original_appointment_id' => 'required|exists:appointments,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        $user = auth()->user();

        // Отримуємо оригінальний запис
        $query = Appointment::with(['client', 'master', 'service']);

        // Майстер може копіювати тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $originalAppointment = $query->findOrFail($request->original_appointment_id);

        // Отримуємо актуальну ціну та тривалість з MasterService
        $masterService = MasterService::where('master_id', $originalAppointment->master_id)
            ->where('service_id', $originalAppointment->service_id)
            ->first();

        $duration = $masterService ? $masterService->getDuration() : $originalAppointment->duration;
        $price = $masterService ? $masterService->price : $originalAppointment->price;

        // Перевірка на конфлікт часу
        $conflict = $this->checkTimeConflict(
            $originalAppointment->master_id,
            $request->appointment_date,
            $request->appointment_time,
            $duration
        );

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'На цей час вже є запис',
            ], 422);
        }

        // Створюємо новий запис
        $newAppointment = Appointment::create([
            'client_id' => $originalAppointment->client_id,
            'master_id' => $originalAppointment->master_id,
            'service_id' => $originalAppointment->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time.':00',
            'duration' => $duration,
            'price' => $price,
            'notes' => $originalAppointment->notes,
            'status' => 'scheduled',
        ]);

        // Відправляємо Telegram повідомлення майстру
        $masterTelegramBotService->sendMasterNotification($newAppointment);

        // Повертаємо дані для оновлення календаря
        return response()->json([
            'success' => true,
            'message' => 'Повторний запис успішно створено',
            'appointment' => [
                'id' => $newAppointment->id,
                'master_id' => $newAppointment->master_id,
                'appointment_date' => $newAppointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $newAppointment->appointment_time,
                'duration' => $newAppointment->duration,
                'client_name' => $originalAppointment->client->name,
                'client_telegram' => $originalAppointment->client->telegram_username,
                'service_name' => $originalAppointment->service->name,
                'price' => $newAppointment->price,
                'status' => $newAppointment->status,
                'telegram_notification_sent' => $newAppointment->telegram_notification_sent,
                'is_confirmed' => $newAppointment->is_confirmed,
            ],
        ]);
    }

    /**
     * Скасувати запис
     */
    public function cancel($id)
    {
        $user = auth()->user();

        $query = Appointment::with(['client', 'master', 'service']);

        // Майстер може скасовувати тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);

        // Перевіряємо чи можна скасувати (адмін може скасовувати в будь-який час)
        if (! $user->isAdmin() && ! $appointment->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Неможливо скасувати запис менше ніж за 24 години до прийому',
            ], 422);
        }

        // Скасовуємо запис
        $appointment->update(['status' => 'cancelled']);

        // Відправляємо сповіщення про скасування
        app(MasterTelegramBotNotificationService::class)->sendCancellationNotification($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Запис успішно скасовано',
        ]);
    }

    /**
     * Перенести запис на новий час
     */
    public function reschedule($id, Request $request)
    {
        $user = auth()->user();

        $query = Appointment::with(['client', 'master', 'service']);

        // Майстер може переносити тільки свої записи
        if ($user->isMaster()) {
            $query->where('master_id', $user->id);
        }

        $appointment = $query->findOrFail($id);

        // Валідація
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
        ]);

        // Перевіряємо чи є конфлік з іншими записами
        $conflict = Appointment::where('master_id', $appointment->master_id)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('status', 'scheduled')
            ->where('id', '!=', $appointment->id)
            ->get();

        $appointmentStart = Carbon::parse($validated['appointment_time']);
        $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration);

        foreach ($conflict as $existing) {
            $existingStart = Carbon::parse($existing->appointment_time);
            $existingEnd = $existingStart->copy()->addMinutes($existing->duration);

            if ($appointmentStart->lt($existingEnd) && $appointmentEnd->gt($existingStart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'На новий час вже є запис',
                ], 422);
            }
        }

        // Оновлюємо запис
        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
        ]);

        // Відправляємо сповіщення про перенесення
        app(MasterTelegramBotNotificationService::class)->sendMasterNotification($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Запис успішно перенесено',
            'appointment' => [
                'id' => $appointment->id,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
            ],
        ]);
    }
}
