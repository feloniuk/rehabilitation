<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        // Реєстрація middleware alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Можна додати глобальні middleware якщо потрібно
        // $middleware->append([
        //     \App\Http\Middleware\SomeGlobalMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Логируємо CSRF помилку для дебагу
            \Illuminate\Support\Facades\Log::warning('CSRF Token Mismatch (419 Error)', [
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'session_driver' => config('session.driver'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);

            // При истекании CSRF токена возвращаем кастомную страницу
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token expired. Please refresh and try again.',
                    'status' => 419,
                ], 419);
            }

            // Возвращаем кастомную страницу ошибки 419
            return response()->view('errors.419', [], 419);
        });
    })->create();
