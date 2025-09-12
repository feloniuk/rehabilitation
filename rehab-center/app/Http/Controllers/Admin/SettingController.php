<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

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
}