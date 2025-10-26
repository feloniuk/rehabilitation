@extends('layouts.admin')

@section('title', $master->name)
@section('page-title', 'Майстер: ' . $master->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Master Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
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
                <p class="text-gray-600">{{ $master->email }}</p>
                @if($master->phone)
                    <p class="text-gray-600">{{ $master->phone }}</p>
                @endif
            </div>
            
            @if($master->description)
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Опис</h3>
                    <p class="text-gray-600 text-sm">{{ $master->description }}</p>
                </div>
            @endif
            
            <div class="mb-6">
                <h3 class="font-semibold mb-2">Статус</h3>
                @if($master->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Активний</span>
                @else
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Неактивний</span>
                @endif
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('admin.masters.edit', $master->id) }}" 
                   class="flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Редагувати
                </a>
                <a href="{{ route('masters.show', $master->id) }}" target="_blank"
                   class="flex-1 text-center bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Переглянути
                </a>
            </div>
        </div>
    </div>

    <!-- Services & Schedule -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Services -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Послуги та ціни</h3>
            @if($master->masterServices->count() > 0)
                <div class="space-y-3">
                    @foreach($master->masterServices as $masterService)
                        <div class="flex justify-between items-center p-3 border rounded">
                            <div>
                                <h4 class="font-medium">{{ $masterService->service->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $masterService->service->description }}</p>
                                <p class="text-sm text-gray-500">Тривалість: {{ $masterService->getDuration() }} хв</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">{{ number_format($masterService->price, 0) }} грн</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Послуги не налаштовані</p>
            @endif
        </div>

        <!-- Work Schedule -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Графік роботи</h3>
            @if($master->work_schedule)
                <div class="space-y-2">
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
                        <div class="flex justify-between py-1">
                            <span>{{ $dayName }}:</span>
                            @if($master->isWorkingOnDay($dayKey))
                                @php $hours = $master->getWorkingHours($dayKey); @endphp
                                <span>{{ $hours['start'] }} - {{ $hours['end'] }}</span>
                            @else
                                <span class="text-gray-400">Вихідний</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Графік не налаштований</p>
            @endif
        </div>

        <!-- Recent Appointments -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Останні записи</h3>
            @if($master->masterAppointments->count() > 0)
                <div class="space-y-3">
                    @foreach($master->masterAppointments->take(5) as $appointment)
                        <div class="flex justify-between items-center p-3 border rounded">
                            <div>
                                <h4 class="font-medium">{{ $appointment->client->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $appointment->service->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $appointment->appointment_date->format('d.m.Y') }} о {{ substr($appointment->appointment_time, 0, 5) }}
                                </p>
                            </div>
                            <div>
                                @if($appointment->status === 'scheduled')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Заплановано</span>
                                @elseif($appointment->status === 'completed')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">Завершено</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Скасовано</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Записів немає</p>
            @endif
        </div>
    </div>
</div>
@endsection
