@extends('layouts.app')

@section('title', $master->name . ' - Спеціаліст')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Головна</a>
            <span class="mx-2">/</span>
            <span>{{ $master->name }}</span>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Master Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center mb-6">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                        @if($master->photo)
                            <img src="{{ asset('storage/' . $master->photo) }}"
                                 alt="{{ $master->name }}"
                                 class="w-full h-full object-cover rounded-full">
                        @else
                            <i class="fas fa-user text-4xl text-gray-500"></i>
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold">{{ $master->name }}</h1>
                    <p class="text-gray-600">Спеціаліст</p>
                </div>

                @if($master->description)
                    <div class="mb-6">
                        <h3 class="font-semibold mb-2">Про спеціаліста</h3>
                        <p class="text-gray-600">{{ $master->description }}</p>
                    </div>
                @endif

                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Режим роботи</h3>
                    @if($master->work_schedule)
                        <div class="space-y-1 text-sm">
                            @php
                                $days = [
                                    'monday' => 'Понеділок',
                                    'tuesday' => 'Вівторок',
                                    'wednesday' => 'Середа',
                                    'thursday' => 'Четвер',
                                    'friday' => 'П\'ятниця',
                                    'saturday' => 'Субота',
                                    'sunday' => 'Неділя'
                                ];
                            @endphp
                            @foreach($days as $dayKey => $dayName)
                                @if($master->isWorkingOnDay($dayKey))
                                    @php $hours = $master->getWorkingHours($dayKey); @endphp
                                    <div class="flex justify-between">
                                        <span>{{ $dayName }}:</span>
                                        <span>{{ $hours['start'] }} - {{ $hours['end'] }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between text-gray-400">
                                        <span>{{ $dayName }}:</span>
                                        <span>Вихідний</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Services & Booking -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold mb-6">Послуги та ціни</h2>

                @if($master->masterServices->count() > 0)
                    <div class="space-y-4">
                        @foreach($master->masterServices as $masterService)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold">{{ $masterService->service->name }}</h3>
                                        <p class="text-gray-600 text-sm">{{ $masterService->service->description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-green-600">{{ number_format($masterService->price, 0) }} грн</p>
                                        <p class="text-sm text-gray-500">{{ $masterService->getDuration() }} хв</p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="{{ route('appointment.create', ['master_id' => $master->id, 'service_id' => $masterService->service->id]) }}" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                                        Записатися
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Послуги не налаштовані.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
