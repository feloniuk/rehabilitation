<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Setting;
use App\Models\User;

class HomeController extends Controller
{
    public function index($tenant)
    {
        $currentTenant = app('currentTenant');
        $services = Service::where('is_active', true)->get();
        $masters = User::masters()->ofTenant()
            ->where('is_active', true)
            ->with('masterServices.service')
            ->get();

        $mapSettings = [
            'address' => Setting::get('center_address', ''),
            'coordinates' => Setting::get('center_coordinates', '50.4501,30.5234'), // Kyiv default
        ];

        return view('home', compact('services', 'masters', 'mapSettings', 'currentTenant'));
    }
}
