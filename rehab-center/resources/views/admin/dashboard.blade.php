@extends('layouts.admin')

@section('title', 'Розклад')
@section('page-title', '')

@section('content')

<!-- Календар контейнер -->
<div id="calendar-container" class="bg-white rounded-lg shadow-sm overflow-hidden">
    
    <!-- 🔝 Верхня панель -->
    <div class="flex items-center justify-between px-4 py-3 border-b bg-white sticky top-0 z-20">
        <div class="flex items-center gap-2">
            <i class="fas fa-calendar text-blue-600 text-lg"></i>
            <span class="font-bold text-lg" id="current-date">
                {{ $calendar['weekDates'][0]->format('d.m') }}
            </span>
        </div>
        <div class="flex gap-2">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="inline">
                <input type="hidden" name="week" value="previous">
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
            </form>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="inline">
                <input type="hidden" name="week" value="0">
                <button type="submit" class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                    Сьогодні
                </button>
            </form>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="inline">
                <input type="hidden" name="week" value="next">
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
            </form>
        </div>
    </div>

<!-- 👥 Блок співробітників + таблиця часу (єдиний flex-контейнер) -->
<div class="timeline-wrapper" style="height: calc(100vh - 340px);">
    <div class="flex flex-col" style="height: inherit;">
        
        <!-- Шапка з майстрами (sticky) -->
        <div class="staff-header bg-white border-b sticky z-10" style="top: 57px;">
            <div class="flex">
                <!-- Колонка часу (ліва) -->
                <div class="flex-shrink-0 w-16 border-r bg-gray-50"></div>
                
                <!-- Майстри -->
                @foreach($calendar['masters'] as $master)
                    <div class="flex-1 staff-column border-r last:border-r-0">
                        <div class="p-3 text-center">
                            @if($master->photo)
                                <img src="{{ asset('storage/' . $master->photo) }}" 
                                     class="w-10 h-10 rounded-full mx-auto mb-2 object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full mx-auto mb-2 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ substr($master->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="text-xs font-semibold text-gray-900 truncate">{{ $master->name }}</div>
                            @if($master->specialty)
                                <div class="text-[10px] text-gray-500 truncate">{{ $master->specialty }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 🕐 Таблиця часу (scrollable) -->
        <div class="timeline-container overflow-y-auto flex-1">
            <div class="flex">
                <!-- Колонка часу -->
                <div class="flex-shrink-0 w-16 border-r bg-gray-50">
                    @foreach($calendar['timeSlots'] as $timeSlot)
                        <div class="time-slot h-20 border-b flex items-start justify-center pt-1">
                            <span class="text-[11px] font-medium text-gray-600">{{ $timeSlot }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Колонки майстрів -->
                @foreach($calendar['masters'] as $masterIndex => $master)
                    <div class="flex-1 staff-column border-r last:border-r-0 relative" data-master-id="{{ $master->id }}">
                        @php
                            $dateKey = $calendar['weekDates'][0]->format('Y-m-d');
                            $dayAppointments = collect($calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? []);
                        @endphp

                        <!-- Сітка часових слотів -->
                        @foreach($calendar['timeSlots'] as $slotIndex => $timeSlot)
                            <div class="time-slot h-20 border-b border-dashed border-gray-200 relative" data-time-slot="{{ $timeSlot }}">
                                @php
                                    $slotAppointments = $dayAppointments->filter(function($apt) use ($timeSlot) {
                                        return substr($apt['time'], 0, 5) === $timeSlot;
                                    });
                                @endphp

                                @foreach($slotAppointments as $apt)
                                    @php
                                        $heightPx = ($apt['duration'] / 30) * 80;
                                        $colors = [
                                            ['from' => '#8B5CF6', 'to' => '#6366F1'],
                                            ['from' => '#3B82F6', 'to' => '#2563EB'],
                                            ['from' => '#10B981', 'to' => '#059669'],
                                        ];
                                        $color = $colors[$masterIndex % 3];
                                        $endTime = \Carbon\Carbon::parse($apt['time'])->addMinutes($apt['duration']);
                                    @endphp
                                    
                                    <div class="appointment-card absolute left-1 right-1 rounded-lg shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow"
                                         style="height: {{ $heightPx }}px; background: linear-gradient(135deg, {{ $color['from'] }}, {{ $color['to'] }}); z-index: 5;"
                                         onclick="showAppointmentDetails({{ $apt['id'] }})">
                                        
                                        <div class="text-white text-xs font-bold mb-1">
                                            {{ substr($apt['time'], 0, 5) }} – {{ $endTime->format('H:i') }}
                                        </div>
                                        
                                        <div class="text-white text-sm font-semibold mb-1 truncate">
                                            {{ $apt['client_name'] }}
                                        </div>
                                        
                                        <div class="text-white text-xs opacity-90 truncate">
                                            {{ $apt['service_name'] }}
                                        </div>

                                        @if($apt['status'] === 'scheduled')
                                            <div class="absolute top-2 right-2">
                                                <span class="text-white text-xs">⏰</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
    <!-- 📆 Нижня панель дат -->
    <div class="border-t bg-white">
        <div class="flex overflow-x-auto hide-scrollbar">
            @foreach($calendar['weekDates'] as $index => $date)
                <button onclick="selectDate({{ $index }})"
                        data-date-index="{{ $index }}"
                        class="date-btn flex-1 min-w-[60px] py-3 text-center border-r last:border-r-0 transition-colors {{ $index === $calendar['todayIndex'] ? 'bg-purple-500 text-white active' : 'hover:bg-gray-50' }}">
                    <div class="text-[10px] font-medium {{ $index === $calendar['todayIndex'] ? 'text-purple-100' : 'text-gray-500' }}">
                        {{ strtoupper($date->isoFormat('dd')) }}
                    </div>
                    <div class="text-lg font-bold mt-1">
                        {{ $date->format('d') }}
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="grid grid-cols-4 gap-2 mt-3">
    <div class="bg-white rounded-lg shadow-sm p-2 text-center">
        <div class="text-xl font-bold text-blue-600">{{ $stats['today'] }}</div>
        <div class="text-[10px] text-gray-500">Сьогодні</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-2 text-center">
        <div class="text-xl font-bold text-green-600">{{ $stats['week'] }}</div>
        <div class="text-[10px] text-gray-500">Тиждень</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-2 text-center">
        <div class="text-xl font-bold text-purple-600">{{ $stats['month'] }}</div>
        <div class="text-[10px] text-gray-500">Місяць</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-2 text-center">
        <div class="text-xl font-bold text-orange-600">{{ $stats['upcoming'] }}</div>
        <div class="text-[10px] text-gray-500">Майбутні</div>
    </div>
</div>

<!-- Модалка -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="appointmentContent" class="p-4"></div>
        <div class="p-4 border-t">
            <button onclick="closeModal()" class="w-full bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.staff-column {
    min-width: 160px;
}

.time-slot {
    position: relative;
}

.appointment-card {
    overflow: hidden;
}

.timeline-wrapper {
    display: flex;
    flex-direction: column;
}

.timeline-container {
    flex: 1;
    overflow-y: auto;
}
</style>
@endpush

@push('scripts')
<script>
var calendarData = {
    scheduleByMaster: @json($calendar['scheduleByMaster']),
    weekDates: @json(collect($calendar['weekDates'])->map(fn($d) => $d->format('Y-m-d'))->values()),
    masters: [
        @foreach($calendar['masters'] as $master)
        {
            id: {{ $master->id }},
            name: "{{ addslashes($master->name) }}",
            photo: "{{ $master->photo ?? '' }}",
            specialty: "{{ addslashes($master->specialty ?? '') }}"
        }@if(!$loop->last),@endif
        @endforeach
    ],
    timeSlots: @json($calendar['timeSlots']),
    todayIndex: {{ $calendar['todayIndex'] }}
};

// Ініціалізація - показуємо сьогоднішній день
var currentDayIndex = calendarData.todayIndex;

// При завантаженні сторінки показуємо сьогодні
document.addEventListener('DOMContentLoaded', function() {
    selectDate(currentDayIndex);
});

function selectDate(index) {
    currentDayIndex = index;
    
    // Оновлюємо кнопки
    document.querySelectorAll('.date-btn').forEach(function(btn, i) {
        if (i === index) {
            btn.classList.add('active', 'bg-purple-500', 'text-white');
            var smallText = btn.querySelector('.text-\\[10px\\]');
            if (smallText) {
                smallText.classList.remove('text-gray-500');
                smallText.classList.add('text-purple-100');
            }
        } else {
            btn.classList.remove('active', 'bg-purple-500', 'text-white');
            var smallText = btn.querySelector('.text-\\[10px\\]');
            if (smallText) {
                smallText.classList.remove('text-purple-100');
                smallText.classList.add('text-gray-500');
            }
        }
    });

    // Оновлюємо дату у хедері
    var date = new Date(calendarData.weekDates[index]);
    document.getElementById('current-date').textContent = 
        date.getDate().toString().padStart(2, '0') + '.' + 
        (date.getMonth() + 1).toString().padStart(2, '0');

    // Оновлюємо сітку
    reloadTimeline(index);
}

function reloadTimeline(dayIndex) {
    var dateKey = calendarData.weekDates[dayIndex];
    
    // Отримуємо всі колонки майстрів (пропускаємо першу - це колонка часу)
    var masterColumns = document.querySelectorAll('.staff-column:not(.w-16)');
    
    masterColumns.forEach(function(col, masterIdx) {
        var masterId = parseInt(col.dataset.masterId);
        var slots = col.querySelectorAll('.time-slot');
        
        slots.forEach(function(slot) {
            // Очищаємо попередні картки
            slot.querySelectorAll('.appointment-card').forEach(function(card) {
                card.remove();
            });
            
            var timeSlot = slot.dataset.timeSlot;
            var masterData = calendarData.scheduleByMaster[masterId];
            
            if (!masterData || !masterData.appointments_by_date || !masterData.appointments_by_date[dateKey]) {
                return;
            }
            
            var appointments = masterData.appointments_by_date[dateKey];
            
            appointments.forEach(function(apt) {
                var aptTime = apt.time.substring(0, 5);
                
                if (aptTime === timeSlot) {
                    var heightPx = (apt.duration / 30) * 80;
                    var colors = [
                        {from: '#8B5CF6', to: '#6366F1'},
                        {from: '#3B82F6', to: '#2563EB'},
                        {from: '#10B981', to: '#059669'}
                    ];
                    var color = colors[masterIdx % 3];
                    
                    var endTime = new Date('2000-01-01 ' + apt.time);
                    endTime.setMinutes(endTime.getMinutes() + parseInt(apt.duration));
                    var endTimeStr = endTime.toTimeString().substring(0, 5);
                    
                    var card = document.createElement('div');
                    card.className = 'appointment-card absolute left-1 right-1 rounded-lg shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow';
                    card.style.cssText = 'height: ' + heightPx + 'px; background: linear-gradient(135deg, ' + color.from + ', ' + color.to + '); z-index: 5;';
                    card.onclick = function() { showAppointmentDetails(apt.id); };
                    card.innerHTML = '<div class="text-white text-xs font-bold mb-1">' + aptTime + ' – ' + endTimeStr + '</div>' +
                        '<div class="text-white text-sm font-semibold mb-1 truncate">' + apt.client_name + '</div>' +
                        '<div class="text-white text-xs opacity-90 truncate">' + apt.service_name + '</div>' +
                        (apt.status === 'scheduled' ? '<div class="absolute top-2 right-2 text-white text-xs">⏰</div>' : '');
                    
                    slot.appendChild(card);
                }
            });
        });
    });
}

function showAppointmentDetails(id) {
    var modal = document.getElementById('appointmentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
    
    fetch('/admin/appointments/' + id)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var statusClasses = {
                'scheduled': 'bg-green-100 text-green-800',
                'completed': 'bg-blue-100 text-blue-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            var sc = statusClasses[d.status];
            
            document.getElementById('appointmentContent').innerHTML = 
                '<div class="space-y-3">' +
                    '<div><div class="text-xs text-gray-500 mb-1">Клієнт</div><div class="font-semibold">' + d.client.name + '</div><div class="text-sm text-gray-600">' + d.client.phone + '</div></div>' +
                    '<div><div class="text-xs text-gray-500 mb-1">Майстер</div><div class="font-medium">' + d.master.name + '</div></div>' +
                    '<div><div class="text-xs text-gray-500 mb-1">Послуга</div><div class="font-medium">' + d.service.name + '</div><div class="text-sm text-gray-600">' + d.service.duration + ' хв</div></div>' +
                    '<div class="flex gap-3"><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Дата</div><div class="font-medium">' + d.appointment_date + '</div></div><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Час</div><div class="font-medium">' + d.appointment_time + '</div></div></div>' +
                    '<div class="flex gap-3"><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Ціна</div><div class="text-lg font-bold text-green-600">' + d.price + '₴</div></div><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Статус</div><span class="inline-block px-2 py-1 text-xs font-semibold rounded-full ' + sc + '">' + d.status_text + '</span></div></div>' +
                    (d.notes ? '<div><div class="text-xs text-gray-500 mb-1">Примітки</div><div class="text-sm bg-gray-50 p-2 rounded">' + d.notes + '</div></div>' : '') +
                '</div>';
        })
        .catch(function() {
            document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8 text-red-500">Помилка завантаження</div>';
        });
}

function closeModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
    document.getElementById('appointmentModal').classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endpush
@endsection