@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('tenant.home', ['tenant' => app('currentTenant')->slug]) }}" class="hover:text-pink-600 transition-colors">Головна</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-gray-700">{{ $page->title }}</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-pink-100 rounded-full mb-6">
            @if($page->slug === 'about')
                <i class="fas fa-info-circle text-2xl text-pink-600"></i>
            @elseif($page->slug === 'contacts')
                <i class="fas fa-map-marker-alt text-2xl text-pink-600"></i>
            @else
                <i class="fas fa-file-alt text-2xl text-pink-600"></i>
            @endif
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">{{ $page->title }}</h1>
        <div class="w-24 h-1 bg-gradient-to-r from-pink-400 to-rose-500 mx-auto rounded-full"></div>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        @if($page->slug === 'contacts')
            <!-- Special layout for contacts -->
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Contact Info -->
                <div class="p-8 lg:p-12">
                    <div class="prose max-w-none">
                        {!! $page->content !!}
                    </div>
                    
                    <!-- Enhanced Contact Cards -->
                    <div class="mt-8 space-y-6">
                        <div class="flex items-start space-x-4 p-6 bg-pink-50 rounded-xl hover:bg-pink-100 transition-colors">
                            <div class="w-12 h-12 bg-pink-200 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-pink-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Адреса</h3>
                                <p class="text-gray-600">{{ \App\Models\Setting::get('center_address') }}</p>
                                <a href="#" class="text-pink-600 hover:text-pink-700 text-sm mt-2 inline-flex items-center">
                                    <i class="fas fa-directions mr-1"></i>
                                    Побудувати маршрут
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 p-6 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                            <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Телефон</h3>
                                <a href="tel:{{ \App\Models\Setting::get('center_phone') }}" 
                                   class="text-blue-600 hover:text-blue-700 font-medium">
                                    {{ \App\Models\Setting::get('center_phone') }}
                                </a>
                                <p class="text-gray-500 text-sm mt-1">Цілодобово</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 p-6 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                            <div class="w-12 h-12 bg-purple-200 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Email</h3>
                                <a href="mailto:{{ \App\Models\Setting::get('center_email') }}" 
                                   class="text-purple-600 hover:text-purple-700 font-medium">
                                    {{ \App\Models\Setting::get('center_email') }}
                                </a>
                                <p class="text-gray-500 text-sm mt-1">Відповімо протягом години</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 p-6 bg-green-50 rounded-xl hover:bg-green-100 transition-colors">
                            <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Режим роботи</h3>
                                <p class="text-green-600 font-medium">{{ \App\Models\Setting::get('working_hours') }}</p>
                                <p class="text-gray-500 text-sm mt-1">Запис за попередньою домовленістю</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div class="bg-gray-100 min-h-96">
                    <div id="map" class="w-full h-full min-h-96 rounded-r-2xl"></div>
                </div>
            </div>
        @else
            <!-- Regular content layout -->
            <div class="p-8 lg:p-12">
                <div class="prose prose-lg max-w-none">
                    {!! $page->content !!}
                </div>
            </div>
        @endif
    </div>

    @if($page->slug === 'about')
        <!-- About page additional sections -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Mission -->
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl p-8 text-center">
                <div class="w-16 h-16 bg-pink-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-bullseye text-2xl text-pink-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Наша місія</h3>
                <p class="text-gray-600 leading-relaxed">
                    Допомогти кожному пацієнту відновити здоров'я та повернутися до активного життя
                </p>
            </div>

            <!-- Values -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 text-center">
                <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-heart text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Наші цінності</h3>
                <p class="text-gray-600 leading-relaxed">
                    Професіоналізм, турбота, індивідуальний підхід та постійний розвиток
                </p>
            </div>

            <!-- Experience -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 text-center">
                <div class="w-16 h-16 bg-purple-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-award text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Наш досвід</h3>
                <p class="text-gray-600 leading-relaxed">
                    Більше 5 років успішної роботи та сотні задоволених пацієнтів
                </p>
            </div>
        </div>
    @endif

    <!-- CTA Section -->
    <div class="mt-12 bg-gradient-to-r from-pink-600 to-rose-600 rounded-2xl p-8 text-center text-white">
        <h3 class="text-2xl font-bold mb-4">Готові розпочати шлях до здоров'я?</h3>
        <p class="text-pink-100 mb-6 text-lg">
            Зв'яжіться з нами прямо зараз та отримайте професійну консультацію
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:{{ \App\Models\Setting::get('center_phone') }}"
               class="inline-flex items-center justify-center bg-white text-pink-600 px-8 py-3 rounded-xl font-bold hover:bg-gray-50 transition-colors">
                <i class="fas fa-phone mr-2"></i>
                Зателефонувати
            </a>
            <a href="{{ route('tenant.home', ['tenant' => app('currentTenant')->slug]) }}#services"
               class="inline-flex items-center justify-center border-2 border-white text-white px-8 py-3 rounded-xl font-bold hover:bg-white hover:text-pink-600 transition-colors">
                <i class="fas fa-calendar-plus mr-2"></i>
                Записатися онлайн
            </a>
        </div>
    </div>
</div>

@if($page->slug === 'contacts')
@push('scripts')
<script>
// Функція для створення Google Maps iframe без API ключа
function initContactsMap() {
    const coordinates = '{{ \App\Models\Setting::get("center_coordinates", "50.4501,30.5234") }}';
    const address = encodeURIComponent('{{ \App\Models\Setting::get("center_address") }}');
    const centerName = encodeURIComponent('{{ \App\Models\Setting::get("center_name") }}');
    
    // Створюємо iframe з Google Maps (не потрібен API ключ)
    const mapContainer = document.getElementById('map');
    if (mapContainer) {
        const iframe = document.createElement('iframe');
        iframe.width = '100%';
        iframe.height = '100%';
        iframe.style.border = '0';
        iframe.style.minHeight = '400px';
        iframe.loading = 'lazy';
        iframe.referrerPolicy = 'no-referrer-when-downgrade';
        
        // Використовуємо Google Maps Embed без API ключа через параметр query
        iframe.src = `https://www.google.com/maps?q=${coordinates}&output=embed&z=16`;
        
        mapContainer.appendChild(iframe);
    }
}

// Альтернативний варіант через OpenStreetMap (якщо Google не працює)
function initOSMMap() {
    const coordinates = '{{ \App\Models\Setting::get("center_coordinates", "50.4501,30.5234") }}';
    const [lat, lng] = coordinates.split(',').map(Number);
    
    const mapContainer = document.getElementById('map');
    if (mapContainer) {
        const iframe = document.createElement('iframe');
        iframe.width = '100%';
        iframe.height = '100%';
        iframe.style.border = '0';
        iframe.style.minHeight = '400px';
        iframe.loading = 'lazy';
        
        // OpenStreetMap не потребує API ключа
        iframe.src = `https://www.openstreetmap.org/export/embed.html?bbox=${lng-0.01},${lat-0.01},${lng+0.01},${lat+0.01}&layer=mapnik&marker=${lat},${lng}`;
        
        mapContainer.appendChild(iframe);
    }
}

// Завантажуємо карту після завантаження сторінки
window.addEventListener('load', function() {
    try {
        initContactsMap();
    } catch (error) {
        console.log('Fallback to OSM map');
        initOSMMap();
    }
});
</script>
@endpush

@push('styles')
<style>
#map {
    position: relative;
    background: #f3f4f6;
}

#map iframe {
    border-radius: 0 1rem 1rem 0;
}

@media (max-width: 1024px) {
    #map iframe {
        border-radius: 0 0 1rem 1rem;
    }
}
</style>
@endpush
@endif

@push('styles')
<style>
/* Enhanced prose styles */
.prose {
    color: #374151;
    line-height: 1.75;
}

.prose h1, .prose h2, .prose h3, .prose h4 {
    color: #1f2937;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose h1 {
    font-size: 2.5rem;
    line-height: 1.2;
}

.prose h2 {
    font-size: 2rem;
    line-height: 1.3;
}

.prose h3 {
    font-size: 1.5rem;
    line-height: 1.4;
}

.prose p {
    margin-bottom: 1.5rem;
}

.prose a {
    color: #10b981;
    text-decoration: underline;
    font-weight: 500;
}

.prose a:hover {
    color: #059669;
}

.prose ul, .prose ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.prose li {
    margin-bottom: 0.5rem;
}

.prose blockquote {
    border-left: 4px solid #10b981;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    background: #f0fdf4;
    padding: 1rem;
    border-radius: 0.5rem;
}

.prose img {
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.prose table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.prose th, .prose td {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: left;
}

.prose th {
    background-color: #f9fafb;
    font-weight: 600;
}
</style>
@endpush
@endsection
