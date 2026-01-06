<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToTenant(): void
    {
        // Auto-assign tenant_id when creating
        static::creating(function ($model) {
            if (!$model->tenant_id && $tenant = app('currentTenant')) {
                $model->tenant_id = $tenant->id;
            }
        });

        // Add global scope to filter by current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenant = app('currentTenant')) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            }
        });
    }

    /**
     * Get the tenant that owns this model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to a specific tenant.
     */
    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Scope a query to include all tenants (bypass global scope).
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
