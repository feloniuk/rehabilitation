<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['owner', 'subscription']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'trial') {
                $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
            }
        }

        $tenants = $query->latest()->paginate(20);

        return view('super-admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        return view('super-admin.tenants.create');
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'owner_id' => $validated['owner_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'trial_ends_at' => isset($validated['trial_days'])
                ? now()->addDays($validated['trial_days'])
                : null,
        ]);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Організацію створено');
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['owner', 'subscription', 'users']);

        $stats = [
            'users_count' => $tenant->users()->count(),
            'masters_count' => $tenant->masters()->count(),
            'clients_count' => $tenant->clients()->count(),
            'services_count' => $tenant->services()->count(),
            'appointments_count' => $tenant->appointments()->count(),
        ];

        return view('super-admin.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug,' . $tenant->id],
            'is_active' => ['boolean'],
            'trial_ends_at' => ['nullable', 'date'],
        ]);

        $tenant->update($validated);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Організацію оновлено');
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Tenant $tenant)
    {
        // Soft considerations: You might want to add soft deletes or additional checks
        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Організацію видалено');
    }

    /**
     * Toggle tenant active status.
     */
    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);

        $status = $tenant->is_active ? 'активовано' : 'деактивовано';

        return back()->with('success', "Організацію {$status}");
    }

    /**
     * Impersonate tenant (login as owner).
     */
    public function impersonate(Tenant $tenant)
    {
        if (!$tenant->owner) {
            return back()->with('error', 'У організації немає власника');
        }

        // Store original user for returning
        session(['impersonating_from' => auth()->id()]);

        auth()->login($tenant->owner);

        return redirect()->route('tenant.admin.dashboard', ['tenant' => $tenant->slug])
            ->with('info', "Ви увійшли як {$tenant->owner->name}");
    }
}
