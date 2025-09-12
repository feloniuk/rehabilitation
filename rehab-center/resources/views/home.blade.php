@extends('layouts.app')

@section('title', 'Головна - Реабілітаційний центр')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
            {{ \App\Models\Setting::get('center_name', 'Реабілітаційний центр') }}
        </h1>
        <p class="text-xl md:text-2xl mb-8 opacity-90">
            Професійна реабілітація та відновлення здоров'я
        </p>
        <a href="#services" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
            Наші послуги
        </a>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Наші послуги</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($services as $service)
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <h3 class="text-xl font-semibold mb-4 text-blue-600">{{ $service->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $service->description }}</p>
                    <p class="text-sm text-gray-500 mb-4">Тривалість: {{ $service->duration }} хв</p>

                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Ціни:</h4>
                        @php
                            $prices = $service->masterServices->pluck('price')->unique()->sort();
                        @endphp
                        @if($prices->count() > 0)
                            <p class="text-lg font-bold text-green-600">
                                від {{ number_format($prices->first(), 0) }} грн
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('services.show', $service->id) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                        Записатися
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Masters Section -->
<section class="bg-gray-100 py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Наші спеціалісти</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($masters as $master)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                    <div class="h-48 bg-gray-300 flex items-center justify-center">
                        @if($master->photo)
                            <img src="{{ asset('storage/' . $master->photo) }}"
                                 alt="{{ $master->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-4xl text-gray-500"></i>
                        @endif
                    </div>

                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">{{ $master->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit($master->description, 100) }}</p>

                        <div class="mb-4">
                            <h4 class="font-semibold mb-2">Послуги:</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($master->masterServices->take(3) as $masterService)
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                        {{ $masterService->service->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <a href="{{ route('masters.show', $master->id) }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                            Детальніше
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection