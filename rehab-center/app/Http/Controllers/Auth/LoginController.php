<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user && ($user->isAdmin() || $user->isMaster())) {
            return route('admin.dashboard');
        }

        return route('home');
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