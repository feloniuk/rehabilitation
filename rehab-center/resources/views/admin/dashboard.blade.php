@extends('layouts.admin')

@section('title', 'Панель управління')
@section('page-title', 'Панель управління')

@section('content')

<!-- Статистичні картки -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs lg:text-sm text-gray-600">Сьогодні</p>
                <p class="text-xl lg:text-2xl font-bold text-gray-800">{{ $stats['today'] }}</p>
            </div>
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-600 text-lg lg:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs lg:text-sm text-gray-600">Тиждень</p>
                <p class="text-xl lg:text-2xl font-bold text-gray-800">{{ $stats['week'] }}</p>
            </div>
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-week text-green-600 text-lg lg:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs lg:text-sm text-gray-600">Місяць</p>
                <p class="text-xl lg:text-2xl font-bold text-gray-800">{{ $stats['month'] }}</p>
            </div>
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600 text-lg lg:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs lg:text-sm text-gray-600">Майбутні</p>
                <p class="text-xl lg:text-2xl font-bold text-gray-800">{{ $stats['upcoming'] }}</p>
            </div>
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-arrow-right text-orange-600 text-lg lg:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Calendar -->
    <div class="lg:col-span-2 order-2 lg:order-1">
        <div class="bg-white rounded-lg shadow p-4 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-4 gap-2">
                <h3 class="text-base lg:text-lg font-semibold">Календар записів</h3>
                <div class="text-xs lg:text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Поточний місяць
                </div>
            </div>
            
            <!-- Легенда -->
            <div class="flex flex-wrap gap-3 mb-4 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                    <span>Заплановано</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-orange-500 rounded mr-1"></div>
                    <span>Кілька записів</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                    <span>Завершено</span>
                </div>
            </div>
            
            <div id="calendar" class="calendar-container"></div>
        </div>
    </div>

    <!-- Recent Appointments -->
    <div class="order-1 lg:order-2">
        <div class="bg-white rounded-lg shadow p-4 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base lg:text-lg font-semibold">Найближчі записи</h3>
                <a href="{{ route('admin.appointments.index') }}" class="text-xs lg:text-sm text-blue-600 hover:text-blue-800">
                    Всі <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            @if($appointments->count() > 0)
                <div class="space-y-3">
                    @foreach($appointments->take(5) as $appointment)
                        <div class="border-l-4 border-blue-500 pl-3 py-2 cursor-pointer hover:bg-gray-50 rounded transition"
                             onclick="showAppointmentDetails({{ $appointment->id }})">
                            <div class="font-semibold text-sm">{{ $appointment->service->name }}</div>
                            <div class="text-gray-600 text-sm">{{ $appointment->client->name }}</div>
                            @if(auth()->user()->isAdmin())
                                <div class="text-gray-500 text-xs">👨‍⚕️ {{ $appointment->master->name }}</div>
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
                    <p class="text-sm">Немає записів</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальне вікно для одиночного запису -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 lg:p-6 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="appointmentContent" class="p-4 lg:p-6"></div>
        
        <div class="flex justify-end p-4 lg:p-6 border-t bg-gray-50 sticky bottom-0">
            <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm lg:text-base">
                Закрити
            </button>
        </div>
    </div>
</div>

<!-- Модальне вікно для групових записів -->
<div id="groupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 lg:p-6 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-users text-orange-500 mr-2"></i>
                Групові записи
            </h3>
            <button onclick="closeGroupModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="groupContent" class="p-4 lg:p-6"></div>
        
        <div class="flex justify-end p-4 lg:p-6 border-t bg-gray-50 sticky bottom-0">
            <button onclick="closeGroupModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
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
    var initialView = window.innerWidth < 768 ? 'listWeek' : 'timeGridWeek';
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: initialView,
        locale: 'uk',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Сьогодні',
            month: 'Місяць',
            week: 'Тиждень',
            day: 'День',
            list: 'Список'
        },
        events: @json($calendar),
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            
            const extendedProps = info.event.extendedProps;
            
            if (extendedProps.isGroup) {
                showGroupModal(extendedProps);
            } else {
                const appointmentId = extendedProps.appointment_id;
                if (appointmentId) {
                    showAppointmentDetails(appointmentId);
                }
            }
        },
        eventDidMount: function(info) {
            // Додаємо підказку для групових записів
            if (info.event.extendedProps.isGroup) {
                info.el.title = info.event.extendedProps.description;
            }
        },
        windowResize: function(view) {
            if (window.innerWidth < 768) {
                calendar.changeView('listWeek');
            }
        }
    });
    
    calendar.render();
    
    window.addEventListener('resize', function() {
        if (window.innerWidth < 768 && calendar.view.type !== 'listWeek') {
            calendar.changeView('listWeek');
        }
    });
});

function showGroupModal(groupData) {
    const appointments = groupData.appointments;
    
    let html = `
        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-4">
            <p class="font-semibold text-orange-800">
                <i class="fas fa-info-circle mr-2"></i>
                На цей час заплановано ${groupData.count} записів
            </p>
        </div>
        <div class="space-y-3">
    `;
    
    appointments.forEach((apt, index) => {
        html += `
            <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition"
                 onclick="showAppointmentDetails(${apt.id})">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-900 mb-1">
                            ${index + 1}. ${apt.service}
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div>
                                <i class="fas fa-user-md text-blue-500 w-5"></i>
                                Майстер: ${apt.master}
                            </div>
                            <div>
                                <i class="fas fa-user text-green-500 w-5"></i>
                                Клієнт: ${apt.client}
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    document.getElementById('groupContent').innerHTML = html;
    document.getElementById('groupModal').classList.remove('hidden');
    document.getElementById('groupModal').classList.add('flex');
}

function closeGroupModal() {
    document.getElementById('groupModal').classList.add('hidden');
    document.getElementById('groupModal').classList.remove('flex');
}

function showAppointmentDetails(appointmentId) {
    // Закриваємо групове модальне вікно якщо воно відкрите
    closeGroupModal();
    
    document.getElementById('appointmentModal').classList.remove('hidden');
    document.getElementById('appointmentModal').classList.add('flex');
    
    document.getElementById('appointmentContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
            <p class="text-gray-500 mt-2 text-sm">Завантаження...</p>
        </div>
    `;
    
    fetch(`/admin/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('appointmentContent').innerHTML = `
                <div class="space-y-4 text-sm lg:text-base">
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
                            <p class="font-medium">${data.appointment_date}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Час</h4>
                            <p class="font-medium">${data.appointment_time}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ціна</h4>
                            <p class="text-lg font-bold text-green-600">${data.price} грн</p>
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
                    <p class="text-red-500 mt-2 text-sm">Помилка завантаження даних</p>
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
        closeGroupModal();
    }
});
</script>

<style>
@media (max-width: 768px) {
    .fc .fc-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fc .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    
    .fc .fc-button {
        padding: 0.4rem 0.65rem;
        font-size: 0.875rem;
    }
    
    .fc .fc-toolbar-title {
        font-size: 1.1rem;
    }
    
    .fc-list-event {
        font-size: 0.875rem;
    }
}

.fc {
    font-size: 0.9rem;
}

.fc-daygrid-event {
    font-size: 0.8rem;
    padding: 2px 4px;
}

.fc-timegrid-event {
    font-size: 0.85rem;
}

/* Підсвітка групових записів */
.fc-event.fc-event-start[style*="background-color: rgb(245, 158, 11)"] {
    border: 2px solid #D97706 !important;
    font-weight: bold;
}
</style>
@endpush
@endsection