<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantRegistrationController extends Controller
{
    /**
     * Show the tenant registration form.
     */
    public function create()
    {
        return view('platform.register');
    }

    /**
     * Handle tenant registration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $tenant = DB::transaction(function () use ($validated) {
            // Create owner user
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin', // Legacy role for backward compatibility
                'is_active' => true,
            ]);

            // Generate unique slug
            $baseSlug = Str::slug($validated['company_name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'owner_id' => $owner->id,
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14),
                'settings' => [
                    'center_name' => $validated['company_name'],
                    'center_phone' => $validated['phone'],
                ],
            ]);

            // Attach owner to tenant with owner role
            $owner->tenants()->attach($tenant->id, ['role' => 'owner']);

            return $tenant;
        });

        // Log in the owner
        auth()->login(User::find($tenant->owner_id));

        return redirect()->route('tenant.admin.dashboard', ['tenant' => $tenant->slug])
            ->with('success', 'Вітаємо! Ваш обліковий запис створено. У вас є 14 днів безкоштовного пробного періоду.');
    }
}
