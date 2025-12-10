<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::where('role', 'client')
                       ->withCount('clientAppointments')
                       ->paginate(10);

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|min:8|confirmed'
        ], [
            'phone.unique' => 'Клієнт з таким телефоном вже існує',
            'email.unique' => 'Клієнт з таким email вже існує'
        ]);

        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'is_active' => true
        ]);

        return redirect()->route('admin.clients.index')
                         ->with('success', 'Клієнта успішно створено');
    }

    public function show($id)
    {
        $client = User::where('role', 'client')
                      ->with(['clientAppointments' => function($query) {
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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'email', 
                Rule::unique('users', 'email')->ignore($client->id)
            ],
            'phone' => [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('users', 'phone')->ignore($client->id)
            ],
            'password' => 'nullable|min:8|confirmed'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->has('is_active')
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $client->update($updateData);

        return redirect()->route('admin.clients.index')
                         ->with('success', 'Дані клієнта оновлено');
    }

    public function destroy($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        
        // Видаляємо старі записи
        $client->clientAppointments()->delete();
        $client->delete();

        return redirect()->route('admin.clients.index')
                         ->with('success', 'Клієнта видалено');
    }
}