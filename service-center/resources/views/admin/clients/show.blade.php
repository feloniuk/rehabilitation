@extends('layouts.admin')

@section('title', $client->name)
@section('page-title', 'Картка клієнта: ' . $client->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Client Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center mb-6">
                @if($client->photo)
                    <div class="w-32 h-32 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center overflow-hidden">
                        <img src="{{ Storage::url($client->photo) }}"
                             alt="{{ $client->name }}"
                             class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-32 h-32 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-4xl font-bold text-blue-600">
                            {{ substr($client->name, 0, 1) }}
                        </span>
                    </div>
                @endif

                <h1 class="text-2xl font-bold">{{ $client->name }}</h1>
                @if($client->email)
                    <p class="text-gray-600">{{ $client->email }}</p>
                @endif
                <p class="text-gray-600">{{ $client->phone }}</p>
            </div>
            
            <div class="mb-6">
                <h3 class="font-semibold mb-2">Статус</h3>
                @if($client->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Активний</span>
                @else
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Неактивний</span>
                @endif
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('tenant.admin.clients.edit', ['tenant' => app('currentTenant')->slug, 'client' => $client->id]) }}" 
                   class="flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Редагувати
                </a>
            </div>
        </div>
    </div>

    <!-- Appointments -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Останні записи</h3>
            @if($client->clientAppointments->count() > 0)
                <div class="space-y-3">
                    @foreach($client->clientAppointments->take(5) as $appointment)
                        <div class="flex justify-between items-center p-3 border rounded">
                            <div>
                                <h4 class="font-medium">{{ $appointment->service->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $appointment->master->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $appointment->appointment_date->format('d.m.Y') }} о {{ substr($appointment->appointment_time, 0, 5) }}
                                </p>
                            </div>
                            <div>
                                @switch($appointment->status)
                                    @case('scheduled')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Заплановано</span>
                                        @break
                                    @case('completed')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">Завершено</span>
                                        @break
                                    @default
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">Скасовано</span>
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center">Записів ще немає</p>
            @endif
        </div>
    </div>
</div>
@endsection