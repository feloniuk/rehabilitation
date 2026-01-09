<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;

class ServiceController extends Controller
{
    public function show($tenant, $service)
    {
        // GlobalScope from BelongsToTenant trait filters by current tenant automatically
        $serviceModel = Service::where('is_active', true)->findOrFail($service);

        $masters = User::masters()->ofTenant()
            ->where('is_active', true)
            ->whereHas('masterServices', function ($query) use ($service) {
                $query->where('service_id', $service);
            })
            ->with(['masterServices' => function ($query) use ($service) {
                $query->where('service_id', $service);
            }])
            ->get();

        return view('services.show', ['service' => $serviceModel, 'masters' => $masters]);
    }
}
