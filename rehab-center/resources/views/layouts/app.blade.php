<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Реабілітаційний центр')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
        
        .navbar-blur {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .smooth-scroll {
            scroll-behavior: smooth;
        }
        
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Floating button animation */
        .floating-btn {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen smooth-scroll">
    <!-- Navigation -->
    <nav class="navbar-blur fixed w-full top-0 z-50 shadow-sm border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-pink-600 hover:text-pink-700 transition-colors">
                        {{-- <i class="fas fa-leaf mr-2"></i> --}}
                        {{ \App\Models\Setting::get('center_name', 'Реабілітаційний центр') }}
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                        Головна
                    </a>
                    <a href="{{ route('home') }}#services" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                        Послуги
                    </a>
                    <a href="{{ route('home') }}#masters" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                        Спеціалісти
                    </a>
                    <a href="{{ route('pages.show', 'about') }}" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                        Про нас
                    </a>
                    <a href="{{ route('pages.show', 'contacts') }}" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">
                        Контакти
                    </a>

                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-pink-600 font-medium">
                                <i class="fas fa-user"></i>
                                <span>{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="py-2">
                                    
                                    <a href="{{ route('admin.dashboard') }}" type="submit" class="w-full text-left px-1 py-2 text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-tachometer-alt mr-1"></i>
                                                Панель управління
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-1 py-2 text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-sign-out-alt mr-2"></i>
                                            Вийти
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-pink-600 p-2">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="px-4 py-4 space-y-3">
                <a href="{{ route('home') }}" class="block text-gray-700 hover:text-pink-600 font-medium">
                    Головна
                </a>
                <a href="{{ route('home') }}#services" class="block text-gray-700 hover:text-pink-600 font-medium">
                    Послуги
                </a>
                <a href="{{ route('home') }}#masters" class="block text-gray-700 hover:text-pink-600 font-medium">
                    Спеціалісти
                </a>
                <a href="{{ route('pages.show', 'about') }}" class="block text-gray-700 hover:text-pink-600 font-medium">
                    Про нас
                </a>
                <a href="{{ route('pages.show', 'contacts') }}" class="block text-gray-700 hover:text-pink-600 font-medium">
                    Контакти
                </a>
                
                @auth
                    @if(auth()->user()->isAdmin() || auth()->user()->isMaster())
                        <a href="{{ route('admin.dashboard') }}" class="block bg-pink-600 text-white px-4 py-2 rounded-lg font-medium">
                            Панель управління
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-700 hover:text-pink-600 font-medium">
                            Вийти
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        @if(session('success'))
            <div class="fixed top-20 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg fade-in-up">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="fixed top-20 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg fade-in-up">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Floating Action Button -->
    <div class="fixed bottom-6 right-6 z-40">
        <a href="tel:{{ \App\Models\Setting::get('center_phone') }}" 
           class="floating-btn bg-pink-600 text-white p-4 rounded-full shadow-2xl hover:bg-pink-700 transition-all duration-300 hover:scale-110">
            <i class="fas fa-phone text-xl"></i>
        </a>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-20">
        <div class="max-w-7xl mx-auto py-16 px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-6">
                        <i class="fas fa-leaf text-pink-400 text-2xl"></i>
                        <h3 class="text-2xl font-bold">{{ \App\Models\Setting::get('center_name') }}</h3>
                    </div>
                    <p class="text-gray-300 mb-6 leading-relaxed">
                        Професійний центр реабілітації з індивідуальним підходом до кожного пацієнта. 
                        Допомагаємо відновити здоров'я та повернутися до активного життя.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-pink-400 transition-colors">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-pink-400 transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-pink-400 transition-colors">
                            <i class="fab fa-telegram text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Швидкі посилання</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-pink-400 transition-colors">Головна</a></li>
                        <li><a href="{{ route('pages.show', 'about') }}" class="text-gray-300 hover:text-pink-400 transition-colors">Про нас</a></li>
                        <li><a href="{{ route('home') }}#services" class="text-gray-300 hover:text-pink-400 transition-colors">Послуги</a></li>
                        <li><a href="{{ route('home') }}#masters" class="text-gray-300 hover:text-pink-400 transition-colors">Спеціалісти</a></li>
                        <li><a href="{{ route('pages.show', 'contacts') }}" class="text-gray-300 hover:text-pink-400 transition-colors">Контакти</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Контакти</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt text-pink-400 mr-3"></i>
                            <span class="text-gray-300">{{ \App\Models\Setting::get('center_address') }}</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-pink-400 mr-3"></i>
                            <a href="tel:{{ \App\Models\Setting::get('center_phone') }}" class="text-gray-300 hover:text-pink-400 transition-colors">
                                {{ \App\Models\Setting::get('center_phone') }}
                            </a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-pink-400 mr-3"></i>
                            <a href="mailto:{{ \App\Models\Setting::get('center_email') }}" class="text-gray-300 hover:text-pink-400 transition-colors">
                                {{ \App\Models\Setting::get('center_email') }}
                            </a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock text-pink-400 mr-3"></i>
                            <span class="text-gray-300">{{ \App\Models\Setting::get('working_hours') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto py-6 px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        © {{ date('Y') }} {{ \App\Models\Setting::get('center_name') }}. Всі права захищені.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-pink-400 text-sm transition-colors">Політика конфіденційності</a>
                        <a href="#" class="text-gray-400 hover:text-pink-400 text-sm transition-colors">Умови використання</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-hide success/error messages
        setTimeout(function() {
            const messages = document.querySelectorAll('.fade-in-up');
            messages.forEach(message => {
                if (message.classList.contains('bg-green-500') || message.classList.contains('bg-red-500')) {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }
            });
        }, 5000);

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('nav');
            if (window.scrollY > 10) {
                navbar.classList.add('shadow-lg');
            } else {
                navbar.classList.remove('shadow-lg');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
