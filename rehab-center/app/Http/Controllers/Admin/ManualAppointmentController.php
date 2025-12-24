<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MasterService;
use App\Models\Service;
use App\Models\User;
use App\Services\MasterTelegramBotNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManualAppointmentController extends Controller
{
    /**
     * Форма створення запису адміністратором
     */
    public function create()
    {
        $masters = User::where('role', 'master')
            ->where('is_active', true)
            ->get();

        $services = Service::where('is_active', true)->get();

        return view('admin.appointments.create', compact('masters', 'services'));
    }

    /**
     * API для пошуку клієнтів (для Select2)
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 15;

        $query = User::where('role', 'client')
            ->select('id', 'name', 'phone', 'email', 'description', 'telegram_username')
            ->orderBy('name', 'asc');

        if ($search && strlen($search) >= 2) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telegram_username', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $clients = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $clients->map(function ($client) {
            return [
                'id' => $client->id,
                'text' => $client->name.' ('.$client->phone.')',
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'telegram_username' => $client->telegram_username,
                'description' => $client->description,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    /**
     * Збереження запису (з можливістю вручну вказати час)
     */
    public function store(Request $request, MasterTelegramBotNotificationService $masterTelegramBotService)
    {
        $rules = [
            'master_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'allow_overlap' => 'nullable|boolean',
            'client_type' => 'required|in:existing,new',
        ];

        if ($request->client_type === 'existing') {
            $rules['existing_client'] = 'required|exists:users,id';
        } else {
            $rules['new_client_name'] = 'required|string|max:255';
            $rules['new_client_phone'] = 'required|string|max:20';
            $rules['new_client_email'] = 'nullable|email';
        }

        $validated = $request->validate($rules);

        // Перевірка що час в майбутньому
        $appointmentDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->appointment_date.' '.substr($request->appointment_time, 0, 5)
        );

        if ($appointmentDateTime->isPast()) {
            return back()->withErrors([
                'appointment_date' => 'Неможливо створити запис на прошедший час. Виберіть дату та час в майбутньому.',
            ])->withInput();
        }

        // Перевірка на конфлікт часу
        if (! $request->boolean('allow_overlap')) {
            $conflict = $this->checkTimeConflict(
                $request->master_id,
                $request->appointment_date,
                $request->appointment_time,
                $request->duration
            );

            if ($conflict) {
                return back()->withErrors([
                    'appointment_time' => 'На цей час вже є запис. Увімкніть "Дозволити нахлест" якщо потрібно створити запис у будь-якому випадку.',
                ])->withInput();
            }
        }

        // Отримання або створення клієнта
        if ($request->client_type === 'existing') {
            $clientId = $request->existing_client;
        } else {
            $normalizedPhone = PhoneHelper::normalize($request->new_client_phone);

            $client = User::updateOrCreate(
                ['phone' => $normalizedPhone],
                [
                    'name' => $request->new_client_name,
                    'email' => $request->new_client_email,
                    'role' => 'client',
                    'password' => bcrypt(str()->random(12)),
                ]
            );
            $clientId = $client->id;
        }

        // Створення запису
        $appointment = Appointment::create([
            'client_id' => $clientId,
            'master_id' => $request->master_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration' => $request->duration,
            'price' => $request->price,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);

        $masterTelegramBotService->sendMasterNotification($appointment);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Запис успішно створено');
    }

    /**
     * Перевірка конфлікту часу
     *
     * ВИПРАВЛЕНО: правильне парсування дати та часу
     */
    private function checkTimeConflict(
        int $masterId,
        string $date,
        string $time,
        int $duration
    ): bool {
        // Створюємо Carbon об'єкт правильно
        // $date може прийти як '2025-10-27' або '2025-10-27 00:00:00'
        // $time приходить як '09:00' або '09:00:00'

        // Спочатку парсимо дату (відкидаємо час якщо він є)
        $dateOnly = Carbon::parse($date)->format('Y-m-d');

        // Тепер створюємо повний datetime
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $dateOnly.' '.substr($time, 0, 5));
        $endTime = $startTime->copy()->addMinutes($duration);

        // Шукаємо існуючі записи на цей день
        $existingAppointments = Appointment::where('master_id', $masterId)
            ->whereDate('appointment_date', $dateOnly)
            ->where('status', 'scheduled')
            ->get();

        foreach ($existingAppointments as $appointment) {
            // Використовуємо методи з моделі
            $existingStart = $appointment->getStartDateTime();
            $existingEnd = $appointment->getEndDateTime();

            // Перевірка перетину часу
            if ($startTime->lt($existingEnd) && $endTime->gt($existingStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * API для отримання ціни послуги майстра
     */
    public function getServicePrice(Request $request)
    {
        $masterId = $request->input('master_id');
        $serviceId = $request->input('service_id');

        $masterService = MasterService::where('master_id', $masterId)
            ->where('service_id', $serviceId)
            ->first();

        if (! $masterService) {
            return response()->json(['error' => 'Послуга не знайдена'], 404);
        }

        return response()->json([
            'price' => $masterService->price,
            'duration' => $masterService->getDuration(),
        ]);
    }

    public function getMasterServices(Request $request)
    {
        $masterId = $request->input('master_id');

        $masterServices = MasterService::where('master_id', $masterId)
            ->with('service')
            ->get();

        if ($masterServices->isEmpty()) {
            return response()->json([
                'error' => 'Послуг не знайдено для цього майстра',
            ], 404);
        }

        $services = $masterServices->map(function ($masterService) {
            return [
                'id' => $masterService->service->id,
                'name' => $masterService->service->name,
                'duration' => $masterService->getDuration(),
                'price' => $masterService->price,
                'master_service_id' => $masterService->id,
            ];
        });

        return response()->json($services);
    }
}
