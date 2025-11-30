@extends('layouts.app')

@section('title', 'Головна - Реабілітаційний центр')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-r  text-white py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="white" fill-opacity="0.4"><circle cx="30" cy="30" r="2"/></g></g></svg>')"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4">
        <div class="text-center">
            <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute bottom-20 right-20 w-32 h-32 bg-white/5 rounded-full blur-2xl animate-bounce"></div>
            
            <!-- Логотип -->
            <div class="mb-8 flex justify-center">
                <img src="{{ asset('logo.png') }}" 
                     alt="{{ \App\Models\Setting::get('center_name', 'Реабілітаційний центр') }}"
                     class="max-w-md w-full h-auto drop-shadow-2xl hover:scale-105 transition-transform duration-300">
            </div>

            {{-- <h1 class="text-5xl md:text-7xl font-bold mb-8 leading-tight">
                {{ \App\Models\Setting::get('center_name', 'Реабілітаційний центр') }}
            </h1>
            <p class="text-xl md:text-3xl mb-12 opacity-90 max-w-4xl mx-auto leading-relaxed">
                {!! \App\Models\TextBlock::get('hero_title', 'Професійна реабілітація та відновлення здоров\'я з турботою про кожного пацієнта') !!}
            </p> --}}
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <a href="#services" class="bg-white text-pink-600 px-10 py-4 rounded-full font-bold text-lg hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-2xl">
                    Наші послуги
                </a>
                <a href="#masters" class="bg-white text-pink-600 px-10 py-4 rounded-full font-bold text-lg hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-2xl">
                    Спеціалісти
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-4xl font-bold text-gray-800 mb-6">
                {!! \App\Models\TextBlock::get('features_title', 'Чому обирають нас?') !!}
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                {!! \App\Models\TextBlock::get('features_subtitle', 'Ми поєднуємо сучасні методи лікування з індивідуальним підходом до кожного пацієнта') !!}
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 fade-in-up delay-100">
                <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <i class="fas fa-user-md text-2xl text-pink-600"></i>
                </div>
                <h3 class="text-xl font-bold text-center mb-4">
                    {!! \App\Models\TextBlock::get('feature_1_title', 'Досвідчені спеціалісти') !!}
                </h3>
                <p class="text-gray-600 text-center leading-relaxed">
                    {!! \App\Models\TextBlock::get('feature_1_text', 'Наші майстри мають багаторічний досвід та постійно підвищують кваліфікацію') !!}
                </p>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 fade-in-up delay-300">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <i class="fas fa-heart text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-center mb-4">
                    {!! \App\Models\TextBlock::get('feature_2_title', 'Індивідуальний підхід') !!}
                </h3>
                <p class="text-gray-600 text-center leading-relaxed">
                    {!! \App\Models\TextBlock::get('feature_2_text', 'Кожна програма реабілітації розробляється з урахуванням особистих потреб') !!}
                </p>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 fade-in-up delay-500">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <i class="fas fa-award text-2xl text-rose-600"></i>
                </div>
                <h3 class="text-xl font-bold text-center mb-4">
                    {!! \App\Models\TextBlock::get('feature_3_title', 'Гарантія результату') !!}
                </h3>
                <p class="text-gray-600 text-center leading-relaxed">
                    {!! \App\Models\TextBlock::get('feature_3_text', 'Ми гарантуємо видимі результати та покращення стану здоров\'я') !!}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-4xl font-bold text-gray-800 mb-6">
                {!! \App\Models\TextBlock::get('services_title', 'Наші послуги') !!}
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                {!! \App\Models\TextBlock::get('services_subtitle', 'Широкий спектр реабілітаційних послуг для відновлення та підтримки здоров\'я') !!}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($services as $index => $service)
                <div class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 flex flex-col h-[480px] scale-in" style="transition-delay: {{ $index * 0.1 }}s;">
                    <!-- Фото послуги - фіксована висота -->
                    <div class="h-48 bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center relative overflow-hidden flex-shrink-0">
                        @if($service->photo)
                            <img src="{{ asset('storage/' . $service->photo) }}" 
                                 alt="{{ $service->name }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <!-- Дефолтна іконка якщо немає фото -->
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-all duration-300"></div>
                            <i class="fas fa-spa text-4xl text-white relative z-10 group-hover:scale-110 transition-transform duration-300"></i>
                        @endif
                        
                        <!-- Декоративні елементи -->
                        <div class="absolute top-4 right-4 w-6 h-6 bg-white/20 rounded-full"></div>
                        <div class="absolute bottom-6 left-6 w-4 h-4 bg-white/30 rounded-full"></div>
                    </div>
                    
                    <!-- Контент з скролом -->
                    <div class="flex flex-col flex-1 min-h-0">
                        <div class="p-6 flex-1 flex flex-col overflow-hidden">
                            <!-- Заголовок - фіксований -->
                            <h3 style="font-size: 1.2rem;" class="font-bold text-gray-800 mb-3 group-hover:text-pink-600 transition-colors flex-shrink-0">
                                {{ $service->name }}
                            </h3>
                            
                            <!-- Опис зі скролом -->
                            <div class="flex-1 overflow-y-auto custom-scrollbar mb-4">
                                <p class="text-gray-600 leading-relaxed">{{ $service->description }}</p>
                            </div>
                            
                            <!-- Інформація - фіксована -->
                            <div class="flex justify-between items-center mb-4 flex-shrink-0">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $service->duration }} хв
                                </span>
                                @php
                                    $prices = $service->masterServices->pluck('price')->unique()->sort();
                                @endphp
                                @if($prices->count() > 0)
                                    <span class="text-lg font-bold text-pink-600">
                                        від {{ number_format($prices->first(), 0) }} грн
                                    </span>
                                @endif
                            </div>

                            <!-- Кнопка - фіксована -->
                            <a href="{{ route('services.show', $service->id) }}"
                               class="block w-full bg-pink-600 text-white px-6 py-3 rounded-full font-semibold text-center hover:bg-pink-700 transition-all duration-300 transform hover:scale-105 flex-shrink-0">
                                Записатися
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Masters Section -->
<section id="masters" class="py-20 bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-4xl font-bold text-gray-800 mb-6">
                {!! \App\Models\TextBlock::get('masters_title', 'Наші спеціалісти') !!}
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                {!! \App\Models\TextBlock::get('masters_subtitle', 'Команда професіоналів з багаторічним досвідом та постійним розвитком') !!}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($masters as $index => $master)
                <div style="display: flex; flex-direction: column; transition-delay: {{ $index * 0.1 }}s;" class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden fade-in-up">
                    <div class="relative h-64 overflow-hidden">
                        @if($master->photo)
                            <img src="{{ asset('storage/' . $master->photo) }}"
                                 alt="{{ $master->name }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                                <i class="fas fa-user text-4xl text-gray-600"></i>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-check mr-1"></i>
                            Доступний
                        </div>
                    </div>

                    <div class="p-6" style="height: 60%; display: flex; flex-direction: column; justify-content: space-between;">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $master->name }}</h3>

                        @if($master->description)
                            <p class="text-gray-600 mb-4 leading-relaxed">{{ Str::limit($master->description, 80) }}</p>
                        @endif

                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Спеціалізації:</p>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    // Отримуємо унікальні послуги за назвою
                                    $uniqueServices = $master->masterServices
                                        ->unique(function ($item) {
                                            return $item->service->name;
                                        })
                                        ->take(3);
                                @endphp
                                
                                @foreach($uniqueServices as $masterService)
                                    <span class="bg-pink-100 text-black-700 px-3 py-1 rounded-full text-xs font-medium">
                                        {{ $masterService->service->name }}
                                    </span>
                                @endforeach
                                
                                @if($master->masterServices->count() > 3)
                                    <span class="bg-gray-100 text-black-600 px-3 py-1 rounded-full text-xs font-medium">
                                        +{{ $master->masterServices->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @php
                            $masterPrices = $master->masterServices->pluck('price');
                            $minPrice = $masterPrices->min();
                            $maxPrice = $masterPrices->max();
                        @endphp
                        @if($minPrice)
                            <div class="mb-4 text-center">
                                <span class="text-lg font-bold text-pink-600">
                                    @if($minPrice == $maxPrice)
                                        {{ number_format($minPrice, 0) }} грн
                                    @else
                                        {{ number_format($minPrice, 0) }} - {{ number_format($maxPrice, 0) }} грн
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <a href="{{ route('masters.show', $master->id) }}"
                               class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-full font-medium text-center hover:bg-gray-200 transition-colors text-sm">
                                Детальніше
                            </a>
                            <a href="{{ route('masters.show', $master->id) }}#services"
                               class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-full font-medium text-center hover:bg-pink-700 transition-colors text-sm">
                                Записатися
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-pink-600 to-rose-700">
    <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h2 class="text-4xl font-bold mb-6 fade-in-up">
            {!! \App\Models\TextBlock::get('cta_title', 'Готові почати шлях до здоров\'я?') !!}
        </h2>
        <p class="text-xl mb-8 opacity-90 fade-in-up delay-200">
            {!! \App\Models\TextBlock::get('cta_subtitle', 'Зв\'яжіться з нами прямо зараз та отримайте професійну консультацію') !!}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center fade-in-up delay-400">
            <a href="tel:{{ \App\Models\Setting::get('center_phone') }}"
               class="bg-white text-pink-600 px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-phone mr-2"></i>
                {{ \App\Models\Setting::get('center_phone') }}
            </a>
            <a href="#services"
               class="border-2 border-white text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white hover:text-pink-600 transition-colors">
                Переглянути послуги
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="group fade-in-up delay-100">
                <div class="text-4xl font-bold text-pink-600 mb-2 group-hover:scale-110 transition-transform">
                    {!! \App\Models\TextBlock::get('stats_specialists_count', $masters->count() . '+') !!}
                </div>
                <div class="text-gray-600 font-medium">
                    {!! \App\Models\TextBlock::get('stats_specialists_label', 'Спеціалістів') !!}
                </div>
            </div>
            <div class="group fade-in-up delay-200">
                <div class="text-4xl font-bold text-blue-600 mb-2 group-hover:scale-110 transition-transform">
                    {{ $services->count() }}+
                </div>
                <div class="text-gray-600 font-medium">
                    {!! \App\Models\TextBlock::get('stats_services_label', 'Видів послуг') !!}
                </div>
            </div>
            <div class="group fade-in-up delay-300">
                <div class="text-4xl font-bold text-rose-600 mb-2 group-hover:scale-110 transition-transform">
                    {!! \App\Models\TextBlock::get('stats_clients_count', '100+') !!}
                </div>
                <div class="text-gray-600 font-medium">
                    {!! \App\Models\TextBlock::get('stats_clients_label', 'Задоволених клієнтів') !!}
                </div>
            </div>
            <div class="group fade-in-up delay-400">
                <div class="text-4xl font-bold text-purple-600 mb-2 group-hover:scale-110 transition-transform">
                    {!! \App\Models\TextBlock::get('stats_experience_count', '5+') !!}
                </div>
                <div class="text-gray-600 font-medium">
                    {!! \App\Models\TextBlock::get('stats_experience_label', 'Років досвіду') !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
/* Кастомний скролбар для опису послуг */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #ec4899 #fce7f3;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #fce7f3;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #ec4899;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #db2777;
}

.custom-scrollbar::-webkit-scrollbar-button {
    display: none;
}

/* Анімація float */
.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Анімації появи */
.fade-in-up {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-in-up.visible {
    opacity: 1;
    transform: translateY(0);
}

.fade-in-left {
    opacity: 0;
    transform: translateX(-30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-in-left.visible {
    opacity: 1;
    transform: translateX(0);
}

.fade-in-right {
    opacity: 0;
    transform: translateX(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-in-right.visible {
    opacity: 1;
    transform: translateX(0);
}

.scale-in {
    opacity: 0;
    transform: scale(0.9);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.scale-in.visible {
    opacity: 1;
    transform: scale(1);
}

/* Затримки для послідовної появи */
.delay-100 { transition-delay: 0.1s; }
.delay-200 { transition-delay: 0.2s; }
.delay-300 { transition-delay: 0.3s; }
.delay-400 { transition-delay: 0.4s; }
.delay-500 { transition-delay: 0.5s; }
.delay-600 { transition-delay: 0.6s; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Настройки Intersection Observer
    const observerOptions = {
        root: null, // viewport
        rootMargin: '0px 0px -100px 0px', // Небольшой отступ снизу
        threshold: 0.1 // Элемент виден на 10%
    };

    // Функция для добавления анимации
    const animateElement = (entry) => {
        entry.target.classList.add('visible');
        // Можно отключить наблюдение после первого появления
        observer.unobserve(entry.target);
    };

    // Создаем Observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateElement(entry);
            }
        });
    }, observerOptions);

    // Элементы для ленивой загрузки
    const lazyElements = document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .scale-in');
    lazyElements.forEach(el => observer.observe(el));
});
</script>
@endpush