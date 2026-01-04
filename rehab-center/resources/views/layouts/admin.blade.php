<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Панель управління')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Sidebar scroll */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #374151;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 2px;
        }

        /* Mobile sidebar animation */
        @media (max-width: 1023px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                height: 100vh;
                background-color: #1F2937;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 50;
                overflow-y: auto;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease;
                z-index: 40;
            }

            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }

            main.content {
                width: 100%;
                max-width: 100vw;
            }
        }

        /* Desktop sidebar */
        @media (min-width: 1024px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 280px;
                overflow-y: auto;
                background-color: #1F2937;
                z-index: 30;
                transition: transform 0.3s ease;
            }

            .content {
                margin-left: 280px;
                width: calc(100% - 280px);
                overflow-x: hidden;
            }
        }

        /* Заборона горизонтальної прокрутки сторінки */
        body {
            overflow-x: hidden;
        }

        main.content {
            overflow-x: hidden;
            max-width: 100%;
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 1rem;
        }

        .pagination .page-item {
            margin: 0 0.25rem;
        }

        .pagination .page-link {
            display: block;
            padding: 0.5rem 0.75rem;
            border: 1px solid #E5E7EB;
            border-radius: 0.375rem;
            color: #374151;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: #3B82F6;
            color: white;
            border-color: #3B82F6;
        }

        .pagination .page-item.disabled .page-link {
            color: #9CA3AF;
            pointer-events: none;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased">
    <div class="flex min-h-screen relative">
        <!-- Sidebar Overlay -->
        <div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar bg-gray-800 text-white sidebar-scroll">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white">Панель</h2>
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-300 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <nav class="py-4">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span class="ml-3">Головна</span>
                    </a>
                    
                    <a href="{{ route('admin.appointments.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.appointments.index') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-calendar-check w-6"></i>
                        <span class="ml-3">Записи</span>
                    </a>
                    
                    <a href="{{ route('admin.appointments.manual.create') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.appointments.manual.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-plus-circle w-6"></i>
                        <span class="ml-3">Створити запис</span>
                    </a>
                    
                    @if(auth()->user()->isAdmin())
                        <div class="border-t border-gray-700 my-2"></div>
                        
                        <a href="{{ route('admin.masters.index') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.masters.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-users w-6"></i>
                            <span class="ml-3">Майстри</span>
                        </a>

                        <a href="{{ route('admin.clients.index') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.clients.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-users w-6"></i>
                            <span class="ml-3">Клієнти</span>
                        </a>
                        
                        <a href="{{ route('admin.services.index') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.services.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-concierge-bell w-6"></i>
                            <span class="ml-3">Послуги</span>
                        </a>
                        
                        <a href="{{ route('admin.pages.index') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.pages.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-file-alt w-6"></i>
                            <span class="ml-3">Сторінки</span>
                        </a>
                        
                        <div class="border-t border-gray-700 my-2"></div>
                        
                        <a href="{{ route('admin.notifications.index') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.notifications.index') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-paper-plane w-6"></i>
                            <span class="ml-3">Розсилки</span>
                        </a>
                        
                        <a href="{{ route('admin.notifications.templates') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.notifications.templates') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-file-alt w-6"></i>
                            <span class="ml-3">Шаблони</span>
                        </a>
                        
                        <a href="{{ route('admin.notifications.logs') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.notifications.logs') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-history w-6"></i>
                            <span class="ml-3">Історія розсилок</span>
                        </a>
                        
                        <div class="border-t border-gray-700 my-2"></div>
                        
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-cog w-6"></i>
                            <span class="ml-3">Налаштування</span>
                        </a>
                    @endif
                    
                    <div class="border-t border-gray-700 mt-4 pt-4">
                        <a href="{{ route('home') }}" 
                           class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                            <i class="fas fa-external-link-alt w-6"></i>
                            <span class="ml-3">На сайт</span>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center w-full px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                                <i class="fas fa-sign-out-alt w-6"></i>
                                <span class="ml-3">Вийти</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 lg:ml-[280px] content">
            <!-- Mobile Header -->
            <header class="sticky top-0 z-20 bg-white shadow-sm border-b lg:border-b-0">
                <div class="px-4 py-3 flex items-center justify-between">
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-900 mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <h1 class="text-lg lg:text-2xl font-semibold text-gray-800 truncate flex-grow">
                        @yield('page-title', 'Панель управління')
                    </h1>
                    
                    <div class="flex items-center space-x-2">
                        <div class="text-gray-600 hidden md:block">
                            {{ auth()->user()->name }}
                        </div>
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-home text-xl"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content Wrapper -->
            <div class="p-4 lg:p-6 bg-gray-100 min-h-screen">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }

        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebar-overlay').classList.remove('open');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>