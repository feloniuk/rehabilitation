@extends('layouts.app')

@section('title', $service->name . ' - Послуги')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Головна</a>
            <span class="mx-2">/</span>
            <span>{{ $service->name }}</span>
        </nav>

        <h1 class="text-3xl font-bold mb-4">{{ $service->name }}</h1>
        <p class="text-gray-600 text-lg">{{ $service->description }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-6">Оберіть спеціаліста</h2>

        @if($masters->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($masters as $master)
                    @php
                        $masterService = $master->masterServices->first();
                    @endphp
                    <div class="border rounded-lg p-4 hover:shadow-md transition duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center mr-4">
                                @if($master->photo)
                                    <img src="{{ asset('storage/' . $master->photo) }}"
                                         alt="{{ $master->name }}"
                                         class="w-full h-full object-cover rounded-full">
                                @else
                                    <i class="fas fa-user text-2xl text-gray-500"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ $master->name }}</h3>
                                <p class="text-sm text-gray-600">Спеціаліст</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-lg font-bold text-green-600">{{ number_format($masterService->price, 0) }} грн</p>
                            <p class="text-sm text-gray-500">Тривалість: {{ $masterService->getDuration() }} хв</p>
                        </div>

                        @if($master->description)
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($master->description, 100) }}</p>
                        @endif

                        <div class="flex space-x-2">
                            <a href="{{ route('masters.show', $master->id) }}"
                               class="flex-1 text-center bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-300">
                                Детальніше
                            </a>
                            <a href="{{ route('appointment.create', ['master_id' => $master->id, 'service_id' => $service->id]) }}" 
                               class="flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                                Записатися
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">На даний момент немає доступних спеціалістів для цієї послуги.</p>
            </div>
        @endif
    </div>
</div>
@endsection