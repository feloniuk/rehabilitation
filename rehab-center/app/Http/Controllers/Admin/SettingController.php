<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'center_name' => Setting::get('center_name', 'Реабілітаційний центр'),
            'center_address' => Setting::get('center_address', ''),
            'center_coordinates' => Setting::get('center_coordinates', '50.4501,30.5234'),
            'center_phone' => Setting::get('center_phone', ''),
            'center_email' => Setting::get('center_email', ''),
            'working_hours' => Setting::get('working_hours', 'Пн-Пт: 9:00-18:00'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'center_name' => 'required|string|max:255',
            'center_address' => 'required|string',
            'center_coordinates' => 'required|string',
            'center_phone' => 'required|string|max:20',
            'center_email' => 'required|email',
            'working_hours' => 'required|string',
        ]);

        foreach ($request->except('_token', '_method') as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Налаштування збережено');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Поточний пароль є обов\'язковим',
            'password.required' => 'Новий пароль є обов\'язковим',
            'password.min' => 'Пароль має містити щонайменше :min символів',
            'password.confirmed' => 'Підтвердження пароля не співпадає',
        ]);

        $user = auth()->user();

        // Перевірка поточного пароля
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Поточний пароль невірний',
            ])->withInput();
        }

        // Перевірка що новий пароль не збігається з поточним
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Новий пароль має відрізнятися від поточного',
            ])->withInput();
        }

        // Оновлення пароля
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Пароль успішно змінено');
    }
}
