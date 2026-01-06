<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MasterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'specialty' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'work_schedule' => 'required|array',
            'services' => 'nullable|array',
            'experience_years' => 'nullable|integer|min:0',
            'clients_count' => 'nullable|integer|min:0',
            'certificates_count' => 'nullable|integer|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $selectedServices = [];
        if ($request->has('services')) {
            foreach ($request->services as $serviceId => $serviceData) {
                if (isset($serviceData['price']) && ! empty($serviceData['price'])) {
                    $selectedServices[$serviceId] = $serviceData;
                }
            }
        }

        if (empty($selectedServices)) {
            return back()->withErrors(['services' => 'Оберіть хоча б одну послугу та вкажіть ціну.'])
                ->withInput();
        }

        $masterData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'master',
            'description' => $request->description,
            'rating' => $request->rating,
            'specialty' => $request->specialty,
            'work_schedule' => $request->work_schedule,
            'is_active' => true,
            'experience_years' => $request->experience_years ?? 0,
            'clients_count' => $request->clients_count ?? 0,
            'certificates_count' => $request->certificates_count ?? 0,
        ];

        $master = User::create($masterData);

        if ($request->hasFile('photo')) {
            try {
                $path = $request->file('photo')->store('masters', 'public');
                $master->update(['photo' => $path]);

                // Перевірка що файл дійсно збережено
                if (! Storage::disk('public')->exists($path)) {
                    Log::error("Photo not saved: {$path}");
                }
            } catch (\Exception $e) {
                Log::error('Error saving photo: '.$e->getMessage());
            }
        }

        foreach ($selectedServices as $serviceId => $serviceData) {
            MasterService::create([
                'master_id' => $master->id,
                'service_id' => $serviceId,
                'price' => $serviceData['price'],
                'duration' => $serviceData['duration'] ?: null,
            ]);
        }

        return redirect()->route('admin.masters.index')
            ->with('success', 'Майстра успішно створено');
    }

    public function update(Request $request, $id)
    {
        $master = User::where('role', 'master')->findOrFail($id);

        // Собираем все чекбоксы услуг вручную
        $serviceCheckboxes = collect($request->all())
            ->filter(function ($value, $key) {
                return str_starts_with($key, 'service_checkbox_');
            })
            ->filter(function ($value) {
                return $value == '1';
            });

        // Собираем услуги с ценой
        $selectedServices = [];
        foreach ($request->input('services', []) as $serviceId => $serviceData) {
            $checkboxKey = "service_checkbox_{$serviceId}";

            if (
                isset($request[$checkboxKey]) &&
                $request[$checkboxKey] == '1' &&
                isset($serviceData['price']) &&
                $serviceData['price'] !== null &&
                $serviceData['price'] !== ''
            ) {
                $selectedServices[$serviceId] = $serviceData;
            }
        }

        // ЖЕСТКАЯ валидация
        if ($selectedServices === []) {
            return back()
                ->withErrors([
                    'services' => 'Потрібно вибрати хоча б одну послугу з встановленою ціною!',
                ])
                ->withInput()
                ->with('error', 'Оберіть та вкажіть ціну принаймні для однієї послуги');
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'description' => $request->description,
            'rating' => $request->rating,
            'specialty' => $request->specialty,
            'work_schedule' => $request->work_schedule,
            'is_active' => $request->has('is_active') ? true : false,
            'experience_years' => $request->experience_years ?? 0,
            'clients_count' => $request->clients_count ?? 0,
            'certificates_count' => $request->certificates_count ?? 0,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            try {
                // Удаляем старую фотографию
                if ($master->photo) {
                    Storage::disk('public')->delete($master->photo);
                }

                // Сохраняем новую
                $path = $request->file('photo')->store('masters', 'public');
                $updateData['photo'] = $path;

                // Проверка сохранения
                if (! Storage::disk('public')->exists($path)) {
                    Log::error("Photo not saved: {$path}");
                }
            } catch (\Exception $e) {
                Log::error('Error saving photo: '.$e->getMessage());
            }
        }

        $master->update($updateData);

        // Полное удаление старых услуг перед добавлением новых
        $master->masterServices()->delete();

        // Создание новых услуг
        foreach ($selectedServices as $serviceId => $serviceData) {
            MasterService::create([
                'master_id' => $master->id,
                'service_id' => $serviceId,
                'price' => $serviceData['price'],
                'duration' => $serviceData['duration'] ?: null,
            ]);
        }

        return redirect()->route('admin.masters.index')
            ->with('success', 'Дані майстра оновлено');
    }

    public function index()
    {
        $masters = User::where('role', 'master')
            ->withCount('masterServices')
            ->paginate(10)
            ->withQueryString();

        return view('admin.masters.index', compact('masters'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->get();
        $defaultSchedule = $this->getDefaultSchedule();

        return view('admin.masters.create', compact('services', 'defaultSchedule'));
    }

    public function show($id)
    {
        $master = User::where('role', 'master')
            ->with(['masterServices.service', 'masterAppointments' => function ($query) {
                $query->orderBy('appointment_date', 'desc')->limit(10);
            }, 'masterAppointments.client'])
            ->findOrFail($id);

        return view('admin.masters.show', compact('master'));
    }

    public function edit($id)
    {
        $master = User::where('role', 'master')
            ->with('masterServices')
            ->findOrFail($id);

        $services = Service::where('is_active', true)->get();

        return view('admin.masters.edit', compact('master', 'services'));
    }

    public function destroy($id)
    {
        $master = User::where('role', 'master')->findOrFail($id);

        if ($master->photo) {
            Storage::disk('public')->delete($master->photo);
        }

        $master->delete();

        return back()->with('success', 'Майстра видалено');
    }

    private function getDefaultSchedule()
    {
        return [
            'monday' => ['start' => '09:00', 'end' => '17:00', 'is_working' => true],
            'tuesday' => ['start' => '09:00', 'end' => '17:00', 'is_working' => true],
            'wednesday' => ['start' => '09:00', 'end' => '17:00', 'is_working' => true],
            'thursday' => ['start' => '09:00', 'end' => '17:00', 'is_working' => true],
            'friday' => ['start' => '09:00', 'end' => '17:00', 'is_working' => true],
            'saturday' => ['start' => '10:00', 'end' => '15:00', 'is_working' => false],
            'sunday' => ['start' => '10:00', 'end' => '15:00', 'is_working' => false],
        ];
    }
}
