<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Панель управління')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white min-h-screen">
            <div class="p-4">
                <h2 class="text-xl font-bold">Панель управління</h2>
                <p class="text-gray-300 text-sm">{{ auth()->user()->name }}</p>
            </div>
            
            <nav class="mt-8">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Головна
                </a>
                
                <!-- Записи - доступно для всех -->
                <a href="{{ route('admin.appointments.index') }}" 
                   class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.appointments.*') ? 'bg-gray-700 text-white' : '' }}">
                    <i class="fas fa-calendar-check mr-3"></i>
                    Записи
                </a>
                
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.masters.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.masters.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-users mr-3"></i>
                        Майстри
                    </a>
                    
                    <a href="{{ route('admin.services.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.services.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-concierge-bell mr-3"></i>
                        Послуги
                    </a>
                    
                    <a href="{{ route('admin.pages.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.pages.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-file-alt mr-3"></i>
                        Сторінки
                    </a>
                    
                    <a href="{{ route('admin.settings.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-cog mr-3"></i>
                        Налаштування
                    </a>
                @endif
                
                <div class="border-t border-gray-700 mt-4 pt-4">
                    <a href="{{ route('home') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                        <i class="fas fa-external-link-alt mr-3"></i>
                        На сайт
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="flex items-center w-full px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Вийти
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <h1 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Панель управління')</h1>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>