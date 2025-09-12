<?php
// app/Http/Controllers/Admin/MasterController.php - ВИПРАВЛЕНА ВАЛІДАЦІЯ
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\MasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'work_schedule' => 'required|array',
            'services' => 'nullable|array', // ЗМІНЕНО: nullable замість required
        ]);

        // Додаткова валідація для послуг
        $selectedServices = [];
        if ($request->has('services')) {
            foreach ($request->services as $serviceId => $serviceData) {
                if (isset($serviceData['price']) && !empty($serviceData['price'])) {
                    $selectedServices[$serviceId] = $serviceData;
                }
            }
        }

        // Перевіряємо чи вибрано хоча б одну послугу
        if (empty($selectedServices)) {
            return back()->withErrors(['services' => 'Оберіть хоча б одну послугу та вкажіть ціну.'])
                        ->withInput();
        }

        $master = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'master',
            'description' => $request->description,
            'work_schedule' => $request->work_schedule,
            'is_active' => true,
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('masters', 'public');
            $master->update(['photo' => $path]);
        }

        // Створюємо тільки вибрані послуги
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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'work_schedule' => 'required|array',
            'services' => 'nullable|array', // ЗМІНЕНО: nullable замість required
        ]);

        // Додаткова валідація для послуг
        $selectedServices = [];
        if ($request->has('services')) {
            foreach ($request->services as $serviceId => $serviceData) {
                if (isset($serviceData['price']) && !empty($serviceData['price'])) {
                    $selectedServices[$serviceId] = $serviceData;
                }
            }
        }

        // Перевіряємо чи вибрано хоча б одну послугу
        if (empty($selectedServices)) {
            return back()->withErrors(['services' => 'Оберіть хоча б одну послугу та вкажіть ціну.'])
                        ->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'description' => $request->description,
            'work_schedule' => $request->work_schedule,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($master->photo) {
                Storage::disk('public')->delete($master->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('masters', 'public');
        }

        $master->update($updateData);

        // Оновлюємо послуги
        $master->masterServices()->delete();
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

    // Інші методи залишаються без змін...
    public function index()
    {
        $masters = User::where('role', 'master')
                      ->with('masterServices')
                      ->paginate(10);
        
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
                     ->with(['masterServices.service', 'masterAppointments' => function($query) {
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

        return redirect()->route('admin.masters.index')
                        ->with('success', 'Майстра видалено');
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