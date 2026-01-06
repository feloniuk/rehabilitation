<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'client')
            ->withCount('clientAppointments');

        // Фільтр по пошуковому запиту
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telegram_username', 'like', "%{$search}%");
            });
        }

        // Фільтр по статусу
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $normalizedPhone = PhoneHelper::normalize($request->phone);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($normalizedPhone) {
                    if (User::where('phone', $normalizedPhone)->exists()) {
                        $fail('Клієнт з таким телефоном вже існує');
                    }
                },
            ],
            'telegram_username' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'email.unique' => 'Клієнт з таким email вже існує',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $normalizedPhone,
            'telegram_username' => $this->normalizeTelegramUsername($request->telegram_username),
            'description' => $request->description,
            'password' => Hash::make(str()->random(16)),
            'role' => 'client',
            'is_active' => true,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('clients', 'public');
        }

        $client = User::create($data);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Клієнта успішно створено');
    }

    public function show($id)
    {
        $client = User::where('role', 'client')
            ->with(['clientAppointments' => function ($query) {
                $query->orderBy('appointment_date', 'desc')->limit(10);
            }, 'clientAppointments.service', 'clientAppointments.master'])
            ->findOrFail($id);

        return view('admin.clients.show', compact('client'));
    }

    public function edit($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $normalizedPhone = PhoneHelper::normalize($request->phone);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'telegram_username' => 'nullable|string|max:100',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($client->id)->whereNotNull('email'),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($normalizedPhone, $client) {
                    $exists = User::where('phone', $normalizedPhone)
                        ->where('id', '!=', $client->id)
                        ->exists();
                    if ($exists) {
                        $fail('Клієнт з таким телефоном вже існує');
                    }
                },
            ],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $normalizedPhone,
            'telegram_username' => $this->normalizeTelegramUsername($request->telegram_username),
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($client->photo) {
                \Storage::disk('public')->delete($client->photo);
            }
            $data['photo'] = $request->file('photo')->store('clients', 'public');
        }

        $client->update($data);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Дані клієнта оновлено');
    }

    public function destroy($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);

        // Видаляємо старі записи
        $client->clientAppointments()->delete();
        $client->delete();

        return back()->with('success', 'Клієнта видалено');
    }

    /**
     * Нормалізує telegram username (видаляє @ якщо є, або повертає null)
     */
    private function normalizeTelegramUsername(?string $username): ?string
    {
        if (empty($username)) {
            return null;
        }

        $username = trim($username);

        // Видаляємо @ з початку якщо є
        if (str_starts_with($username, '@')) {
            $username = substr($username, 1);
        }

        return $username ?: null;
    }
}
