<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show super admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users' => User::count(),
            'total_appointments' => Appointment::withoutGlobalScope('tenant')->count(),
            'active_subscriptions' => Subscription::active()->count(),
            'trial_tenants' => Tenant::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now())
                ->count(),
        ];

        $recentTenants = Tenant::with('owner')
            ->latest()
            ->limit(10)
            ->get();

        $recentSubscriptions = Subscription::with('tenant')
            ->latest()
            ->limit(10)
            ->get();

        return view('super-admin.dashboard', compact('stats', 'recentTenants', 'recentSubscriptions'));
    }
}
