<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Models\MasterService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $perPage = 20;

        $query = User::where('role', 'client')
            ->orderBy('name', 'asc');

        // Пошук по імені або телефону
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $clients = $query->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

        // Форматуємо результат для Select2
        $results = $clients->map(function($client) {
            return [
                'id' => $client->id,
                'text' => $client->name . ' - ' . $client->phone,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Збереження запису (з можливістю вручну вказати час)
     */
    public function store(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:15',
            'notes' => 'nullable|string',
            
            // Дані клієнта
            'existing_client' => 'nullable|exists:users,id',
            'new_client_name' => 'required_without:existing_client|string|max:255',
            'new_client_phone' => 'required_without:existing_client|string|max:20',
            'new_client_email' => 'nullable|email',
            
            // Дозвіл на нахлест
            'allow_overlap' => 'nullable|boolean',
        ]);

        // Перевірка на конфлікт часу (якщо не дозволено нахлест)
        if (!$request->boolean('allow_overlap')) {
            $conflict = $this->checkTimeConflict(
                $request->master_id,
                $request->appointment_date,
                $request->appointment_time,
                $request->duration
            );

            if ($conflict) {
                return back()->withErrors([
                    'appointment_time' => 'На цей час вже є запис. Увімкніть "Дозволити нахлест" якщо потрібно створити запис у будь-якому випадку.'
                ])->withInput();
            }
        }

        // Отримання або створення клієнта
        if ($request->filled('existing_client')) {
            $clientId = $request->existing_client;
        } else {
            $client = User::updateOrCreate(
                ['phone' => $request->new_client_phone],
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

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Запис успішно створено');
    }

    /**
     * Перевірка конфлікту часу
     */
    private function checkTimeConflict(
        int $masterId,
        string $date,
        string $time,
        int $duration
    ): bool {
        $startTime = Carbon::parse("$date $time");
        $endTime = $startTime->copy()->addMinutes($duration);

        $existingAppointments = Appointment::where('master_id', $masterId)
            ->where('appointment_date', $date)
            ->where('status', 'scheduled')
            ->get();

        foreach ($existingAppointments as $appointment) {
            $existingStart = Carbon::parse("{$appointment->appointment_date} {$appointment->appointment_time}");
            $existingEnd = $existingStart->copy()->addMinutes($appointment->duration);

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

        if (!$masterService) {
            return response()->json(['error' => 'Послуга не знайдена'], 404);
        }

        return response()->json([
            'price' => $masterService->price,
            'duration' => $masterService->getDuration(),
        ]);
    }
}