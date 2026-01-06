<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionActiveMiddleware
{
    /**
     * Handle an incoming request.
     * Check if tenant has active subscription or is on trial.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return $next($request);
        }

        // Super admins bypass subscription check
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Check if tenant has active subscription or is on trial
        if (!$tenant->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Subscription required',
                    'subscription_status' => 'inactive',
                ], 402);
            }

            // Allow access to billing pages even without subscription
            if ($request->routeIs('tenant.admin.billing.*')) {
                return $next($request);
            }

            return redirect()->route('tenant.admin.billing.index', ['tenant' => $tenant->slug])
                ->with('warning', 'Для продовження роботи необхідно оформити підписку');
        }

        return $next($request);
    }
}
