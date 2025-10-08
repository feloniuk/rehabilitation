@extends('layouts.admin')

@section('title', 'Панель управління')
@section('page-title', 'Панель управління')

@section('content')

<!-- Статистичні картки -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Сьогодні</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['today'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Цей тиждень</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['week'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-week text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Цей місяць</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['month'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Майбутні</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['upcoming'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-arrow-right text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Calendar -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Календар записів</h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Поточний місяць
                </div>
            </div>
            <div id="calendar" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Recent Appointments -->
    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Найближчі записи</h3>
                <a href="{{ route('admin.appointments.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Всі записи →
                </a>
            </div>

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
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                    <p>Немає записів на обраний період</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Таблиця записів -->
<div class="mt-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold">{{ $periodTitle }}</h3>
                <p class="text-sm text-gray-600">Всього записів: {{ $appointments->total() }}</p>
            </div>

            <!-- Фільтри періоду -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.dashboard', ['period' => 'today']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-calendar-day mr-1"></i>
                    Сьогодні
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'week']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-calendar-week mr-1"></i>
                    Тиждень
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'month']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Місяць
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'upcoming']) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-arrow-right mr-1"></i>
                    Майбутні
                </a>
            </div>
        </div>

        @if($appointments->count() > 0)
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
                {{ $appointments->appends(['period' => $period])->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg mb-4">Записів на обраний період не знайдено</p>
                <a href="{{ route('admin.dashboard', ['period' => 'upcoming']) }}" 
                   class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Переглянути майбутні записи
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Модальне вікно з деталями запису -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="appointmentContent" class="p-6">
            <!-- Завантаження через AJAX -->
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
        initialView: 'timeGridWeek',
        locale: 'uk',
        height: 500,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: @json($calendar),
        eventClick: function(info) {
            const appointmentId = info.event.extendedProps.appointment_id;
            if (appointmentId) {
                showAppointmentDetails(appointmentId);
            }
        }
    });
    calendar.render();
});

function showAppointmentDetails(appointmentId) {
    document.getElementById('appointmentModal').classList.remove('hidden');
    document.getElementById('appointmentModal').classList.add('flex');
    
    document.getElementById('appointmentContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
            <p class="text-gray-500 mt-2">Завантаження...</p>
        </div>
    `;
    
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endpush
@endsection