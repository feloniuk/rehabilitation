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
                        <div class="border-l-4 border-blue-500 pl-4 py-2 cursor-pointer hover:bg-gray-50 rounded"
                             onclick="showAppointmentDetails({{ $appointment->id }})">
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
                        <a href="{{ route('admin.appointments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
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
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="showAppointmentDetails({{ $appointment->id }})">
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

<!-- Модальное окно с деталями записи -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="appointmentContent" class="p-6">
            <!-- Содержимое будет загружено через AJAX -->
        </div>
        
        <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
            <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Закрити
            </button>
        </div>
    </div>
</div>

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
            // Извлечь ID записи из события (нужно добавить в контроллер)
            const appointmentId = info.event.extendedProps.appointment_id;
            if (appointmentId) {
                showAppointmentDetails(appointmentId);
            } else {
                alert(info.event.title);
            }
        }
    });
    calendar.render();
});

function showAppointmentDetails(appointmentId) {
    // Показать модальное окно
    document.getElementById('appointmentModal').classList.remove('hidden');
    document.getElementById('appointmentModal').classList.add('flex');
    
    // Показать загрузку
    document.getElementById('appointmentContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
            <p class="text-gray-500 mt-2">Завантаження...</p>
        </div>
    `;
    
    // Загрузить данные
    fetch(`/admin/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('appointmentContent').innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Клієнт</h4>
                        <p class="text-lg font-medium">${data.client.name}</p>
                        <p class="text-sm text-gray-600">${data.client.phone}</p>
                        <p class="text-sm text-gray-600">${data.client.email}</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Майстер</h4>
                        <p class="text-lg font-medium">${data.master.name}</p>
                        <p class="text-sm text-gray-600">${data.master.phone || ''}</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Послуга</h4>
                        <p class="text-lg font-medium">${data.service.name}</p>
                        <p class="text-sm text-gray-600">Тривалість: ${data.service.duration} хв</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Дата</h4>
                            <p class="text-lg font-medium">${data.appointment_date}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Час</h4>
                            <p class="text-lg font-medium">${data.appointment_time}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ціна</h4>
                            <p class="text-lg font-medium text-green-600">${data.price} грн</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Статус</h4>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(data.status)}">
                                ${data.status_text}
                            </span>
                        </div>
                    </div>
                    
                    ${data.notes ? `
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Примітки</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">${data.notes}</p>
                    </div>
                    ` : ''}
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Створено</h4>
                        <p class="text-sm text-gray-600">${data.created_at}</p>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('appointmentContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-400"></i>
                    <p class="text-red-500 mt-2">Помилка завантаження даних</p>
                </div>
            `;
        });
}

function closeModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
    document.getElementById('appointmentModal').classList.remove('flex');
}

function getStatusClass(status) {
    switch(status) {
        case 'scheduled': return 'bg-green-100 text-green-800';
        case 'completed': return 'bg-blue-100 text-blue-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

// Закрытие модального окна по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endpush
@endsection