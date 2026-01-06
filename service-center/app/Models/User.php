<?php

namespace App\Models;

use App\Helpers\PhoneHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'telegram_username', 'telegram_chat_id', 'role', 'description',
        'photo', 'work_schedule', 'is_active', 'is_super_admin', 'rating',
        'experience_years', 'clients_count', 'certificates_count', 'specialty',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'work_schedule' => 'array',
        'is_active' => 'boolean',
        'is_super_admin' => 'boolean',
        'experience_years' => 'integer',
        'clients_count' => 'integer',
        'certificates_count' => 'integer',
    ];

    /**
     * Нормалізує телефон при збереженні
     */
    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = PhoneHelper::normalize($value);
    }

    /**
     * Знаходить користувача за телефоном (з нормалізацією)
     */
    public static function findByPhone(string $phone): ?self
    {
        return self::where('phone', PhoneHelper::normalize($phone))->first();
    }

    // Relationship methods
    public function masterServices()
    {
        return $this->hasMany(MasterService::class, 'master_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'master_services', 'master_id', 'service_id')
            ->withPivot('price', 'duration');
    }

    public function clientAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id');
    }

    public function masterAppointments()
    {
        return $this->hasMany(Appointment::class, 'master_id');
    }

    /**
     * Get all tenants this user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get tenants where user is owner.
     */
    public function ownedTenants(): BelongsToMany
    {
        return $this->tenants()->wherePivot('role', 'owner');
    }

    /**
     * Get the current tenant from the app container.
     */
    public function currentTenant(): ?Tenant
    {
        return app('currentTenant');
    }

    /**
     * Get user's role in a specific tenant.
     */
    public function roleInTenant(Tenant $tenant): ?string
    {
        $pivot = $this->tenants()->where('tenant_id', $tenant->id)->first();
        return $pivot?->pivot?->role;
    }

    /**
     * Check if user has a specific role in a tenant.
     */
    public function hasRoleInTenant(Tenant $tenant, string|array $roles): bool
    {
        $userRole = $this->roleInTenant($tenant);

        if (!$userRole) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($userRole, $roles);
    }

    /**
     * Check if user belongs to a tenant.
     */
    public function belongsToTenant(Tenant $tenant): bool
    {
        return $this->tenants()->where('tenant_id', $tenant->id)->exists();
    }

    // Helper methods

    /**
     * Check if user is a super admin (global platform admin).
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin ?? false;
    }

    /**
     * Check if user is admin in current tenant context.
     * Falls back to legacy role field if no tenant context.
     */
    public function isAdmin(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->currentTenant();

        if ($tenant) {
            return $this->hasRoleInTenant($tenant, ['owner', 'admin']);
        }

        // Legacy: fallback to role field
        return $this->role === 'admin';
    }

    /**
     * Check if user is master in current tenant context.
     * Falls back to legacy role field if no tenant context.
     */
    public function isMaster(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->currentTenant();

        if ($tenant) {
            return $this->hasRoleInTenant($tenant, 'master');
        }

        // Legacy: fallback to role field
        return $this->role === 'master';
    }

    /**
     * Check if user is client in current tenant context.
     * Falls back to legacy role field if no tenant context.
     */
    public function isClient(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->currentTenant();

        if ($tenant) {
            return $this->hasRoleInTenant($tenant, 'client');
        }

        // Legacy: fallback to role field
        return $this->role === 'client';
    }

    /**
     * Check if user is owner in current tenant context.
     */
    public function isOwner(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->currentTenant();

        if (!$tenant) {
            return false;
        }

        return $this->hasRoleInTenant($tenant, 'owner');
    }

    /**
     * Check if user can manage the tenant (owner or admin).
     */
    public function canManageTenant(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->currentTenant();

        if (!$tenant) {
            return false;
        }

        return $this->hasRoleInTenant($tenant, ['owner', 'admin']);
    }

    public function getWorkingDays()
    {
        if (! $this->work_schedule) {
            return [];
        }

        return collect($this->work_schedule)
            ->filter(fn ($day) => $day['is_working'] ?? false)
            ->keys()
            ->toArray();
    }

    public function isWorkingOnDay($dayName)
    {
        return $this->work_schedule[$dayName]['is_working'] ?? false;
    }

    public function getWorkingHours($dayName)
    {
        if (! $this->isWorkingOnDay($dayName)) {
            return null;
        }

        return [
            'start' => $this->work_schedule[$dayName]['start'] ?? '09:00',
            'end' => $this->work_schedule[$dayName]['end'] ?? '17:00',
        ];
    }
}
