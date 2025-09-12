<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Реабілітаційний центр')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-blue-600">
                        {{ \App\Models\Setting::get('center_name', 'Реабілітаційний центр') }}
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">Головна</a>
                    <a href="{{ route('pages.show', 'about') }}" class="text-gray-700 hover:text-blue-600">Про нас</a>
                    <a href="{{ route('pages.show', 'contacts') }}" class="text-gray-700 hover:text-blue-600">Контакти</a>

                    @auth
                        @if(auth()->user()->isAdmin() || auth()->user()->isMaster())
                            <a href="{{ route('admin.dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Панель управління
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-600">Вийти</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Увійти</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700">Головна</a>
                <a href="{{ route('pages.show', 'about') }}" class="block px-3 py-2 text-gray-700">Про нас</a>
                <a href="{{ route('pages.show', 'contacts') }}" class="block px-3 py-2 text-gray-700">Контакти</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-4 mt-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-4 mt-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto py-12 px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Контакти</h3>
                    <p class="mb-2">{{ \App\Models\Setting::get('center_address') }}</p>
                    <p class="mb-2">{{ \App\Models\Setting::get('center_phone') }}</p>
                    <p class="mb-2">{{ \App\Models\Setting::get('center_email') }}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Режим роботи</h3>
                    <p>{{ \App\Models\Setting::get('working_hours') }}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Наше розташування</h3>
                    <div id="map" class="h-48 bg-gray-600 rounded"></div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Google Maps
        function initMap() {
            const coordinates = '{{ \App\Models\Setting::get("center_coordinates", "50.4501,30.5234") }}';
            const [lat, lng] = coordinates.split(',').map(Number);

            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: { lat, lng }
            });

            new google.maps.Marker({
                position: { lat, lng },
                map: map,
                title: '{{ \App\Models\Setting::get("center_name") }}'
            });
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>

    @stack('scripts')
</body>
</html>