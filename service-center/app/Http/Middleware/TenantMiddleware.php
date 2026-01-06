<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenant');

        if (!$slug) {
            return $next($request);
        }

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            abort(404, 'Організацію не знайдено');
        }

        if (!$tenant->is_active) {
            abort(403, 'Організацію деактивовано');
        }

        // Bind tenant to app container
        app()->instance('currentTenant', $tenant);

        // Share tenant with all views
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
