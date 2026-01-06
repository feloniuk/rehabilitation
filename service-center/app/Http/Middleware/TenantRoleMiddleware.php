<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  Allowed roles (owner, admin, master, client)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $tenant = app('currentTenant');
        $user = auth()->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('login');
        }

        if (!$tenant) {
            abort(403, 'Контекст організації не визначено');
        }

        // Super admins cannot access tenant admin panels
        // They only have access to /super-admin routes
        if ($user->isSuperAdmin()) {
            abort(403, 'Super админи не можуть доступатися до панелі організацій. Використовуйте /super-admin');
        }

        $userRole = $user->roleInTenant($tenant);

        if (!$userRole) {
            abort(403, 'Ви не є учасником цієї організації');
        }

        if (!in_array($userRole, $roles)) {
            abort(403, 'Недостатньо прав доступу');
        }

        return $next($request);
    }
}
