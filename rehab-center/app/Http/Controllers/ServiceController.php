<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;

class ServiceController extends Controller
{
    public function show($id)
    {
        $service = Service::where('is_active', true)->findOrFail($id);

        $masters = User::where('role', 'master')
                      ->where('is_active', true)
                      ->whereHas('masterServices', function($query) use ($id) {
                          $query->where('service_id', $id);
                      })
                      ->with(['masterServices' => function($query) use ($id) {
                          $query->where('service_id', $id);
                      }])
                      ->get();

        return view('services.show', compact('service', 'masters'));
    }
}