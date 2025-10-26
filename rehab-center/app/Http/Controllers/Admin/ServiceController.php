<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('masterServices')->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $serviceData = [
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration,
            'is_active' => true,
        ];

        $service = Service::create($serviceData);

        // Завантаження фото
        if ($request->hasFile('photo')) {
            try {
                $path = $request->file('photo')->store('services', 'public');
                $service->update(['photo' => $path]);
                
                // Перевірка що файл дійсно збережено
                if (!Storage::disk('public')->exists($path)) {
                    Log::error("Service photo not saved: {$path}");
                }
            } catch (\Exception $e) {
                Log::error("Error saving service photo: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.services.index')
                        ->with('success', 'Послугу успішно створено');
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:15',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration,
            'is_active' => $request->has('is_active'),
        ];

        // Видалення фото (якщо запит на видалення)
        if ($request->has('delete_photo') && $request->delete_photo == '1') {
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
                $updateData['photo'] = null;
            }
        }
        // Завантаження нового фото
        elseif ($request->hasFile('photo')) {
            try {
                // Видаляємо старе фото
                if ($service->photo) {
                    Storage::disk('public')->delete($service->photo);
                }
                
                // Зберігаємо нове
                $path = $request->file('photo')->store('services', 'public');
                $updateData['photo'] = $path;
                
                // Перевірка збереження
                if (!Storage::disk('public')->exists($path)) {
                    Log::error("Service photo not saved: {$path}");
                }
            } catch (\Exception $e) {
                Log::error("Error saving service photo: " . $e->getMessage());
            }
        }

        $service->update($updateData);

        return redirect()->route('admin.services.index')
                        ->with('success', 'Послугу оновлено');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        
        // Видаляємо фото якщо є
        if ($service->photo) {
            Storage::disk('public')->delete($service->photo);
        }
        
        $service->delete();

        return redirect()->route('admin.services.index')
                        ->with('success', 'Послугу видалено');
    }
}