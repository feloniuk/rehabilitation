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
            <span class="font-bold text-lg" id="current-date">28.10</span>
        </div>
        <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100">
            <i class="fas fa-filter text-gray-600"></i>
        </button>
    </div>

    <!-- 👥 Блок співробітників (фіксований при скролі) -->
    <div class="staff-header bg-white border-b sticky z-10" style="top: 57px;">
        <div class="flex overflow-x-auto hide-scrollbar">
            <!-- Колонка часу (ліва) -->
            <div class="flex-shrink-0 w-16 border-r"></div>
            
            <!-- Майстри -->
            @foreach($calendar['masters'] as $master)
                <div class="flex-shrink-0 staff-column border-r last:border-r-0">
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

    <!-- 🕐 Таблиця часу (Timeline Grid) -->
    <div class="timeline-container" style="height: calc(100vh - 280px); overflow-y: auto;">
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
                <div class="flex-1 staff-column border-r last:border-r-0 relative">
                    @php
                        $dateKey = $calendar['weekDates'][0]->format('Y-m-d'); // Поточний день
                        $dayAppointments = collect($calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? []);
                    @endphp

                    <!-- Сітка часових слотів -->
                    @foreach($calendar['timeSlots'] as $slotIndex => $timeSlot)
                        <div class="time-slot h-20 border-b border-dashed border-gray-200 relative">
                            @php
                                // Знаходимо записи для цього слоту
                                $slotAppointments = $dayAppointments->filter(function($apt) use ($timeSlot) {
                                    return substr($apt['time'], 0, 5) === $timeSlot;
                                });
                            @endphp

                            @foreach($slotAppointments as $apt)
                                @php
                                    // Розрахунок висоти блоку (1 хвилина = 1.33px при слоті 30хв = 40px)
                                    $heightPx = ($apt['duration'] / 30) * 80;
                                    $colors = [
                                        ['from' => '#8B5CF6', 'to' => '#6366F1'], // фіолетовий
                                        ['from' => '#3B82F6', 'to' => '#2563EB'], // синій
                                        ['from' => '#10B981', 'to' => '#059669'], // зелений
                                    ];
                                    $color = $colors[$masterIndex % 3];
                                @endphp
                                
                                <!-- 📦 Блок запису -->
                                <div class="appointment-card absolute left-1 right-1 rounded-lg shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow"
                                     style="height: {{ $heightPx }}px; background: linear-gradient(135deg, {{ $color['from'] }}, {{ $color['to'] }}); z-index: 5;"
                                     onclick="showAppointmentDetails({{ $apt['id'] }})">
                                    
                                    <!-- Час -->
                                    <div class="text-white text-xs font-bold mb-1">
                                        {{ substr($apt['time'], 0, 5) }} – {{ \Carbon\Carbon::parse($apt['time'])->addMinutes($apt['duration'])->format('H:i') }}
                                    </div>
                                    
                                    <!-- Ім'я клієнта -->
                                    <div class="text-white text-sm font-semibold mb-1 truncate">
                                        {{ $apt['client_name'] }}
                                    </div>
                                    
                                    <!-- Послуга -->
                                    <div class="text-white text-xs opacity-90 truncate">
                                        {{ $apt['service_name'] }}
                                    </div>

                                    <!-- Іконки статусів -->
                                    <div class="absolute top-2 right-2">
                                        @if($apt['status'] === 'scheduled')
                                            <span class="text-white text-xs">⚠️</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <!-- 📆 Нижня панель дат -->
    <div class="border-t bg-white">
        <div class="flex overflow-x-auto hide-scrollbar">
            @foreach($calendar['weekDates'] as $index => $date)
                <button onclick="selectDate({{ $index }})"
                        data-date-index="{{ $index }}"
                        class="date-btn flex-1 min-w-[60px] py-3 text-center border-r last:border-r-0 transition-colors {{ $date->isToday() ? 'bg-purple-500 text-white' : 'hover:bg-gray-50' }}">
                    <div class="text-[10px] font-medium {{ $date->isToday() ? 'text-purple-100' : 'text-gray-500' }}">
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
    min-width: 140px;
    width: 140px;
}

.time-slot {
    position: relative;
}

.appointment-card {
    overflow: hidden;
    font-size: 11px;
}

.date-btn.active {
    background-color: #8B5CF6 !important;
    color: white !important;
}

.date-btn.active .text-gray-500 {
    color: rgba(255,255,255,0.8) !important;
}

/* Поточний час лінія */
.current-time-line {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background: #EF4444;
    z-index: 10;
}

.current-time-line::before {
    content: '';
    position: absolute;
    left: -6px;
    top: -4px;
    width: 10px;
    height: 10px;
    background: #EF4444;
    border-radius: 50%;
}
</style>
@endpush

@push('scripts')
<script>
// Завантаження даних для інших днів
const calendarData = {
    scheduleByMaster: {
        @foreach($calendar['scheduleByMaster'] as $masterId => $masterData)
            {{ $masterId }}: {
                appointments_by_date: {
                    @foreach($masterData['appointments_by_date'] as $date => $appointments)
                        '{{ $date }}': [
                            @foreach($appointments as $apt)
                                {
                                    id: {{ $apt['id'] }},
                                    time: '{{ $apt['time'] }}',
                                    duration: {{ $apt['duration'] }},
                                    client_name: '{{ addslashes($apt['client_name']) }}',
                                    service_name: '{{ addslashes($apt['service_name']) }}',
                                    price: {{ $apt['price'] }},
                                    status: '{{ $apt['status'] }}'
                                },
                            @endforeach
                        ],
                    @endforeach
                }
            },
        @endforeach
    },
    weekDates: [
        @foreach($calendar['weekDates'] as $d)
            '{{ $d->format('Y-m-d') }}',
        @endforeach
    ],
    masters: [
        @foreach($calendar['masters'] as $m)
            {
                id: {{ $m->id }},
                name: '{{ addslashes($m->name) }}',
                photo: '{{ $m->photo }}',
                specialty: '{{ addslashes($m->specialty ?? '') }}'
            },
        @endforeach
    ],
    timeSlots: @json($calendar['timeSlots'])
};

console.log('Calendar data loaded:', calendarData);

let currentDayIndex = 0;

function selectDate(index) {
    currentDayIndex = index;
    
    // Оновлюємо кнопки
    document.querySelectorAll('.date-btn').forEach((btn, i) => {
        if (i === index) {
            btn.classList.add('active', 'bg-purple-500', 'text-white');
        } else {
            btn.classList.remove('active', 'bg-purple-500', 'text-white');
        }
    });

    // Оновлюємо дату у хедері
    const date = new Date(calendarData.weekDates[index]);
    document.getElementById('current-date').textContent = 
        date.getDate().toString().padStart(2, '0') + '.' + 
        (date.getMonth() + 1).toString().padStart(2, '0');

    // Оновлюємо сітку
    reloadTimeline(index);
}

function reloadTimeline(dayIndex) {
    const dateKey = calendarData.weekDates[dayIndex];
    const staffColumns = document.querySelectorAll('.staff-column');
    
    // Пропускаємо першу колонку (це час)
    const masterColumns = Array.from(staffColumns).slice(1);
    
    masterColumns.forEach((col, masterIdx) => {
        const masterId = calendarData.masters[masterIdx].id;
        const slots = col.querySelectorAll('.time-slot');
        
        slots.forEach((slot, slotIdx) => {
            // Очищаємо попередні картки
            slot.querySelectorAll('.appointment-card').forEach(card => card.remove());
            
            const timeSlot = calendarData.timeSlots[slotIdx];
            const appointments = calendarData.scheduleByMaster[masterId]?.appointments_by_date?.[dateKey] || [];
            
            console.log('Loading for date:', dateKey, 'master:', masterId, 'slot:', timeSlot, 'appointments:', appointments);
            
            appointments.forEach(apt => {
                const aptTime = apt.time.substring(0, 5);
                if (aptTime === timeSlot) {
                    const heightPx = (apt.duration / 30) * 80;
                    const colors = [
                        {from: '#8B5CF6', to: '#6366F1'},
                        {from: '#3B82F6', to: '#2563EB'},
                        {from: '#10B981', to: '#059669'}
                    ];
                    const color = colors[masterIdx % 3];
                    
                    const endTime = new Date('2000-01-01 ' + apt.time);
                    endTime.setMinutes(endTime.getMinutes() + parseInt(apt.duration));
                    const endTimeStr = endTime.toTimeString().substring(0, 5);
                    
                    const card = document.createElement('div');
                    card.className = 'appointment-card absolute left-1 right-1 rounded-lg shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow';
                    card.style.cssText = `height: ${heightPx}px; background: linear-gradient(135deg, ${color.from}, ${color.to}); z-index: 5;`;
                    card.onclick = () => showAppointmentDetails(apt.id);
                    card.innerHTML = `
                        <div class="text-white text-xs font-bold mb-1">${aptTime} – ${endTimeStr}</div>
                        <div class="text-white text-sm font-semibold mb-1 truncate">${apt.client_name}</div>
                        <div class="text-white text-xs opacity-90 truncate">${apt.service_name}</div>
                        ${apt.status === 'scheduled' ? '<div class="absolute top-2 right-2 text-white text-xs">⚠️</div>' : ''}
                    `;
                    slot.appendChild(card);
                }
            });
        });
    });
}

function showAppointmentDetails(id) {
    const modal = document.getElementById('appointmentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
    
    fetch('/admin/appointments/' + id)
        .then(r => r.json())
        .then(d => {
            const sc = {'scheduled':'bg-green-100 text-green-800','completed':'bg-blue-100 text-blue-800','cancelled':'bg-red-100 text-red-800'}[d.status];
            document.getElementById('appointmentContent').innerHTML = `
                <div class="space-y-3">
                    <div><div class="text-xs text-gray-500 mb-1">Клієнт</div><div class="font-semibold">${d.client.name}</div><div class="text-sm text-gray-600">${d.client.phone}</div></div>
                    <div><div class="text-xs text-gray-500 mb-1">Майстер</div><div class="font-medium">${d.master.name}</div></div>
                    <div><div class="text-xs text-gray-500 mb-1">Послуга</div><div class="font-medium">${d.service.name}</div><div class="text-sm text-gray-600">${d.service.duration} хв</div></div>
                    <div class="flex gap-3"><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Дата</div><div class="font-medium">${d.appointment_date}</div></div><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Час</div><div class="font-medium">${d.appointment_time}</div></div></div>
                    <div class="flex gap-3"><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Ціна</div><div class="text-lg font-bold text-green-600">${d.price}₴</div></div><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Статус</div><span class="inline-block px-2 py-1 text-xs font-semibold rounded-full ${sc}">${d.status_text}</span></div></div>
                    ${d.notes ? `<div><div class="text-xs text-gray-500 mb-1">Примітки</div><div class="text-sm bg-gray-50 p-2 rounded">${d.notes}</div></div>` : ''}
                </div>
            `;
        })
        .catch(() => {
            document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8 text-red-500">Помилка</div>';
        });
}

function closeModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
    document.getElementById('appointmentModal').classList.remove('flex');
}

document.addEventListener('keydown', e => e.key === 'Escape' && closeModal());
</script>
@endpush
@endsection