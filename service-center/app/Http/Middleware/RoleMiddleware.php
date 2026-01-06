<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Used for global roles like super_admin or legacy role field.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check for super_admin role
        if (in_array('super_admin', $roles) && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Legacy role check (from user.role field)
        $userRole = $user->role;

        if (!in_array($userRole, $roles)) {
            abort(403, 'Недостатньо прав доступу');
        }

        return $next($request);
    }
}