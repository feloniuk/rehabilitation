<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ServiceCenter - SaaS платформа для сервісних центрів')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('platform.home') }}" class="text-xl font-bold text-indigo-600">
                        ServiceCenter
                    </a>
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="{{ route('platform.features') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2">
                            Можливості
                        </a>
                        <a href="{{ route('platform.pricing') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2">
                            Ціни
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('platform.select-tenant') }}" class="text-gray-600 hover:text-gray-900">
                            Мої організації
                        </a>
                        <form method="POST" action="{{ route('platform.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">
                                Вийти
                            </button>
                        </form>
                    @else
                        <a href="{{ route('platform.login') }}" class="text-gray-600 hover:text-gray-900">
                            Увійти
                        </a>
                        <a href="{{ route('platform.register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Почати безкоштовно
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="text-center text-gray-400">
                &copy; {{ date('Y') }} ServiceCenter. Всі права захищено.
            </div>
        </div>
    </footer>
</body>
</html>
