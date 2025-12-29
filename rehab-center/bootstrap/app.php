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
            // При истекании CSRF токена редиректим на логин
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token expired'], 419);
            }

            return redirect()->route('login');
        });
    })->create();
