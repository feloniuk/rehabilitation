<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the global login form.
     */
    public function showLogin()
    {
        return view('platform.login');
    }

    /**
     * Handle global login.
     * After login, redirect to tenant selection or directly to tenant dashboard.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Невірний email або пароль.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Super admin goes to super admin panel
        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        // Get user's tenants
        $tenants = $user->tenants;

        if ($tenants->isEmpty()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Ви не є учасником жодної організації.',
            ])->onlyInput('email');
        }

        // If user has only one tenant, redirect directly
        if ($tenants->count() === 1) {
            $tenant = $tenants->first();
            $role = $user->roleInTenant($tenant);

            // Masters and clients go to home, owners and admins go to admin
            if (in_array($role, ['owner', 'admin', 'master'])) {
                return redirect()->route('tenant.admin.dashboard', ['tenant' => $tenant->slug]);
            }

            return redirect()->route('tenant.home', ['tenant' => $tenant->slug]);
        }

        // Multiple tenants - show selection page
        return redirect()->route('platform.select-tenant');
    }

    /**
     * Show tenant selection page for users with multiple tenants.
     */
    public function selectTenant()
    {
        $user = Auth::user();
        $tenants = $user->tenants()->get();

        if ($tenants->isEmpty()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Ви не є учасником жодної організації.',
            ]);
        }

        return view('platform.select-tenant', compact('tenants'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.home');
    }
}
