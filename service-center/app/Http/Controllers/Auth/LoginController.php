<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo()
    {
        // Если была 419 ошибка - перенаправляем туда где она произошла
        if (session()->has('redirect_after_login')) {
            $redirect = session()->pull('redirect_after_login');
            // Проверяем что это не внешний URL (защита от redirect attacks)
            if (str_starts_with($redirect, url('/'))) {
                return $redirect;
            }
        }

        $user = auth()->user();

        if ($user && ($user->isAdmin() || $user->isMaster())) {
            // Redirect to first tenant's admin dashboard
            $tenant = $user->tenants()->first();
            if ($tenant) {
                return route('tenant.admin.dashboard', ['tenant' => $tenant->slug]);
            }
        }

        return route('platform.home');
    }

    /**
     * Create a new controller instance.
     * В Laravel 12 middleware реєструється через маршрути
     */
    public function __construct()
    {
        // Видаляємо middleware з конструктора - вони тепер в маршрутах
    }
}
