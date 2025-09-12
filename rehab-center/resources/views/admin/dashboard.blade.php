@extends('layouts.admin')

@section('title', 'Панель управління')
@section('page-title', 'Панель управління')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Calendar -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Календар записів</h3>
            <div id="calendar" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Recent Appointments -->
    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Найближчі записи</h3>

            @if($appointments->count() > 0)
                <div class="space-y-3">
                    @foreach($appointments->take(5) as $appointment)
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <div class="font-semibold text-sm">{{ $appointment->service->name }}</div>
                            <div class="text-gray-600 text-sm">{{ $appointment->client->name }}</div>
                            @if(auth()->user()->isAdmin())
                                <div class="text-gray-500 text-xs">{{ $appointment->master->name }}</div>
                            @endif
                            <div class="text-gray-500 text-xs">
                                {{ $appointment->appointment_date->format('d.m.Y') }} о {{ substr($appointment->appointment_time, 0, 5) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($appointments->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.dashboard') }}?show_all=1" class="text-blue-600 hover:text-blue-800 text-sm">
                            Показати всі записи
                        </a>
                    </div>
                @endif
            @else
                <p class="text-gray-500">Немає найближчих записів</p>
            @endif
        </div>
    </div>
</div>

@if(request('show_all'))
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Всі записи</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клієнт</th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Майстер</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Послуга</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Час</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($appointments as $appointment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $appointment->client->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $appointment->client->phone }}</div>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $appointment->master->name }}
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->service->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->appointment_date->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ substr($appointment->appointment_time, 0, 5) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($appointment->status === 'scheduled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Заплановано
                                        </span>
                                    @elseif($appointment->status === 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Завершено
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Скасовано
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t">
                {{ $appointments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'uk',
        height: 500,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: @json($calendar),
        eventClick: function(info) {
            alert(info.event.title);
        }
    });
    calendar.render();
});
</script>
@endpush
@endsection