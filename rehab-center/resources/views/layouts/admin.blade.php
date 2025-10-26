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
        /* Приховати скролбар для sidebar */
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
        
        /* Анімація для мобільного меню */
        .sidebar-mobile {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-mobile.open {
            transform: translateX(0);
        }
        
        /* Overlay для мобільного меню */
        .sidebar-overlay {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
        }
        .sidebar-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }
        
        /* Фіксований sidebar на десктопі */
        @media (min-width: 1024px) {
            .sidebar-desktop {
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
            }
        }

        .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        gap: 0.25rem;
        margin: 0;
        justify-content: center;
    }

    /* Всі елементи списку */
    .pagination .page-item {
        margin: 0;
    }

    /* Всі посилання та span */
    .pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25;
        color: #374151;
        text-decoration: none;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    /* Ховер ефект */
    .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
        color: #1f2937;
    }

    /* Активна сторінка */
    .pagination .page-item.active .page-link {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        font-weight: 600;
        cursor: default;
    }

    /* Неактивні кнопки (disabled) */
    .pagination .page-item.disabled .page-link {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #d1d5db;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Focus стан для accessibility */
    .pagination .page-link:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        box-shadow: none;
    }

    /* Прибираємо зайві відступи */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        border-radius: 0.375rem;
    }

    /* ================================================
    АДАПТИВНІСТЬ ДЛЯ МОБІЛЬНИХ
    ================================================ */
    @media (max-width: 640px) {
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .pagination .page-link {
            min-width: 2rem;
            height: 2rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    /* ================================================
    ВАРІАНТИ КОЛЬОРІВ (ОПЦІОНАЛЬНО)
    Додайте клас до nav або батківського div
    ================================================ */

    /* Зелений варіант */
    .pagination-green .pagination .page-item.active .page-link {
        background-color: #10b981;
        border-color: #10b981;
    }

    .pagination-green .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
        border-color: #10b981;
        color: #10b981;
    }

    /* Фіолетовий варіант */
    .pagination-purple .pagination .page-item.active .page-link {
        background-color: #8b5cf6;
        border-color: #8b5cf6;
    }

    .pagination-purple .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
        border-color: #8b5cf6;
        color: #8b5cf6;
    }

    /* Помаранчевий варіант */
    .pagination-orange .pagination .page-item.active .page-link {
        background-color: #f59e0b;
        border-color: #f59e0b;
    }

    .pagination-orange .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
        border-color: #f59e0b;
        color: #f59e0b;
    }

    /* ================================================
    ВАРІАНТИ РОЗМІРІВ
    ================================================ */

    /* Компактний */
    .pagination-compact .pagination .page-link {
        min-width: 2rem;
        height: 2rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Mobile Menu Overlay -->
        <div id="sidebar-overlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-mobile lg:transform-none fixed lg:static inset-y-0 left-0 z-50 w-64 bg-gray-800 text-white lg:min-h-screen overflow-y-auto sidebar-scroll">
            <!-- Header з кнопкою закриття на мобільному -->
            <div class="p-4 flex items-center justify-between border-b border-gray-700">
                <div>
                    <h2 class="text-xl font-bold">Панель управління</h2>
                    <p class="text-gray-300 text-sm">{{ auth()->user()->name }}</p>
                </div>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-300 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <nav class="mt-4 pb-4">
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

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Mobile Header -->
            <header class="bg-white shadow-sm border-b lg:border-b-0 sticky top-0 z-30">
                <div class="px-4 py-3 flex items-center justify-between">
                    <!-- Mobile Menu Button -->
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <h1 class="text-lg lg:text-2xl font-semibold text-gray-800 truncate">
                        @yield('page-title', 'Панель управління')
                    </h1>
                    
                    <!-- Right side icons (можна додати нотифікації, профіль і т.д.) -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 lg:hidden">
                            <i class="fas fa-home text-xl"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 p-4 lg:p-6 bg-gray-100">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle mt-0.5 mr-2"></i>
                            <span class="flex-1">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                            <span class="flex-1">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
        
        // Закривати меню при кліку на посилання на мобільному
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });
        
        // Закривати меню при зміні розміру вікна
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
