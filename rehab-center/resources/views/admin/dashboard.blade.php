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

<!-- 👥 Блок співробітників + таблиця часу -->
<div class="timeline-wrapper" style="height: calc(100vh - 340px);">
    <div class="flex flex-col" style="height: inherit;">
        
        <!-- Шапка з майстрами (sticky) -->
        <div class="staff-header bg-white border-b sticky z-10" style="top: 57px;">
            <div class="flex">
                <!-- Колонка часу (ліва) -->
                <div class="flex-shrink-0 w-16 border-r bg-gray-50"></div>
                
                <!-- Майстри -->
                @foreach($calendar['masters'] as $master)
                    <div class="flex-1 staff-column border-r last:border-r-0" style="margin-right: 8px;">
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
                    <div class="flex-1 staff-column border-r last:border-r-0 relative" 
                         data-master-id="{{ $master->id }}"
                         data-master-index="{{ $masterIndex }}">
                        @php
                            $dateKey = $calendar['weekDates'][0]->format('Y-m-d');
                            $dayAppointments = collect($calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? []);
                        @endphp

                        <!-- Сітка часових слотів -->
                        @foreach($calendar['timeSlots'] as $slotIndex => $timeSlot)
                            <div class="time-slot h-20 border-b border-dashed border-gray-200 relative" 
                                 data-time-slot="{{ $timeSlot }}"
                                 data-slot-index="{{ $slotIndex }}">
                            </div>
                        @endforeach

                        <!-- ОКРЕМО: рендеримо всі записи цього майстра в абсолютному позиціонуванні -->
                        @foreach($dayAppointments as $aptIndex => $apt)
                            @php
                                // Обчислюємо позицію картки
                                $startTime = \Carbon\Carbon::parse($apt['time']);
                                $endTime = $startTime->copy()->addMinutes($apt['duration']);
                                
                                // Знаходимо початковий слот (перший слот >= startTime)
                                $slotStartMinutes = null;
                                foreach($calendar['timeSlots'] as $idx => $slot) {
                                    $slotTime = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                    if ($slotTime->lte($startTime)) {
                                        $slotStartMinutes = $slotTime->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', '00:00'));
                                    }
                                }
                                
                                // Початок дня (перший слот)
                                $dayStartTime = \Carbon\Carbon::createFromFormat('H:i', $calendar['timeSlots'][0]);
                                $dayStartMinutes = $dayStartTime->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', '00:00'));
                                
                                // Початок запису в хвилинах від початку дня
                                $aptStartMinutes = $startTime->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', '00:00'));
                                
                                // Offset від початку дня
                                $minutesFromDayStart = $aptStartMinutes - $dayStartMinutes;
                                
                                // Висота одного слота = 80px (h-20)
                                // Один слот = 30 хвилин
                                $pixelsPerMinute = 80 / 30; // 2.666px per minute
                                
                                $topPx = $minutesFromDayStart * $pixelsPerMinute;
                                $heightPx = $apt['duration'] * $pixelsPerMinute;
                                
                                $colors = [
                                    ['from' => '#8B5CF6', 'to' => '#6366F1'],
                                    ['from' => '#3B82F6', 'to' => '#2563EB'],
                                    ['from' => '#10B981', 'to' => '#059669'],
                                    ['from' => '#F59E0B', 'to' => '#D97706'],
                                    ['from' => '#EF4444', 'to' => '#DC2626'],
                                ];
                                $color = $colors[($masterIndex + $aptIndex) % count($colors)];
                                
                                // Виявляємо нахлести з іншими записами
                                $overlappingCount = 0;
                                $positionInOverlap = 0;
                                
                                foreach($dayAppointments as $otherIndex => $otherApt) {
                                    $otherStart = \Carbon\Carbon::parse($otherApt['time']);
                                    $otherEnd = $otherStart->copy()->addMinutes($otherApt['duration']);
                                    
                                    // Перевірка перетину: A.start < B.end AND A.end > B.start
                                    if ($startTime->lt($otherEnd) && $endTime->gt($otherStart)) {
                                        $overlappingCount++;
                                        if ($otherIndex < $aptIndex) {
                                            $positionInOverlap++;
                                        }
                                    }
                                }
                                
                                $widthPercent = $overlappingCount > 1 ? (100 / $overlappingCount) : 100;
                                $leftPercent = $positionInOverlap * $widthPercent;
                            @endphp
                            
                            <div class="appointment-card absolute rounded-lg shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow"
                                 data-appointment-id="{{ $apt['id'] }}"
                                 style="top: {{ $topPx }}px;
                                        height: {{ $heightPx }}px; 
                                        background: linear-gradient(135deg, {{ $color['from'] }}, {{ $color['to'] }}); 
                                        z-index: {{ 5 + $aptIndex }}; 
                                        left: {{ $leftPercent }}%;
                                        width: calc({{ $widthPercent }}% - 4px);"
                                 onclick="showAppointmentDetails({{ $apt['id'] }})">
                                
                                <div class="text-white text-xs font-bold mb-1">
                                    {{ substr($apt['time'], 0, 5) }} – {{ $endTime->format('H:i') }}
                                    @if($apt['telegram_notification_sent'] === true)<span class="ml-1">📨</span>@endif
                                </div>

                                <div class="text-white text-sm font-semibold mb-1 truncate flex items-center">
                                    {{ $apt['client_name'] }}
                                    @if(!empty($apt['client_telegram']))
                                        <a href="https://t.me/{{ $apt['client_telegram'] }}" target="_blank"
                                           onclick="event.stopPropagation()"
                                           class="ml-1 text-white hover:text-blue-200 flex-shrink-0"
                                           title="@{{ $apt['client_telegram'] }}">
                                            <i class="fab fa-telegram"></i>
                                        </a>
                                    @endif
                                </div>

                                <div class="text-white text-xs opacity-90 truncate">
                                    {{ $apt['service_name'] }}
                                </div>
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
                        class="date-btn flex-1 min-w-[60px] py-3 text-center border-r last:border-r-0 transition-colors {{ $index === $calendar['todayIndex'] ? 'bg-purple-500 text-white active' : 'hover:bg-purple-200' }}">
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
        <div class="p-4 border-t flex flex-col gap-2">
            <button id="confirmButton"
                    onclick="toggleConfirmation()"
                    class="w-full px-4 py-3 border-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 border-gray-300 bg-white text-gray-600 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span id="confirmButtonText">Підтвердити</span>
            </button>
            <div class="flex gap-2">
                <button id="quickReminderBtn" onclick="sendQuickReminder()" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Швидке нагадування "на завтра"</span>
                </button>
                <button onclick="closeModal()" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">
                    Закрити
                </button>
            </div>
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
    todayIndex: {{ $calendar['todayIndex'] }},
    selectDateRoute: "{{ route('admin.select-date') }}"
};

var currentDayIndex = {{ $selectedDateIndex ?? 0 }};
var currentAppointmentId = null;

document.addEventListener('DOMContentLoaded', function() {
    selectDate(currentDayIndex);
    scrollToCurrentHour();
});

function scrollToCurrentHour() {
    // Отримуємо поточний час (без хвилин)
    var now = new Date();
    var currentHour = now.getHours();
    var currentMinute = now.getMinutes();
    var currentTimeStr = currentHour.toString().padStart(2, '0') + ':' + currentMinute.toString().padStart(2, '0');

    // Шукаємо індекс слота, який <= поточному часу
    var firstSlotTime = calendarData.timeSlots[0];
    var firstSlotParts = firstSlotTime.split(':');
    var firstSlotMinutes = parseInt(firstSlotParts[0]) * 60 + parseInt(firstSlotParts[1]);

    var targetSlotIndex = 0;
    for (var i = 0; i < calendarData.timeSlots.length; i++) {
        var slotParts = calendarData.timeSlots[i].split(':');
        var slotMinutes = parseInt(slotParts[0]) * 60 + parseInt(slotParts[1]);
        var nowMinutes = currentHour * 60 + currentMinute;

        if (slotMinutes <= nowMinutes) {
            targetSlotIndex = i;
        } else {
            break;
        }
    }

    // Кожен слот має висоту 80px (h-20)
    var slotHeightPx = 80;
    var scrollPosition = targetSlotIndex * slotHeightPx;

    // Скролимо контейнер
    var timelineContainer = document.querySelector('.timeline-container');
    if (timelineContainer) {
        timelineContainer.scrollTop = scrollPosition;
    }
}

function selectDate(index) {
    currentDayIndex = index;

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

    var date = new Date(calendarData.weekDates[index]);
    document.getElementById('current-date').textContent =
        date.getDate().toString().padStart(2, '0') + '.' +
        (date.getMonth() + 1).toString().padStart(2, '0');

    reloadTimeline(index);

    // Зберігаємо вибрану дату в сесію через AJAX
    fetch(calendarData.selectDateRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            date_index: index
        })
    }).catch(function(error) {
        console.error('Error saving date:', error);
    });
}

function reloadTimeline(dayIndex) {
    var dateKey = calendarData.weekDates[dayIndex];
    var masterColumns = document.querySelectorAll('.staff-column[data-master-id]');
    
    masterColumns.forEach(function(col) {
        var masterId = parseInt(col.dataset.masterId);
        var masterIdx = parseInt(col.dataset.masterIndex);
        
        // Видаляємо всі старі картки
        col.querySelectorAll('.appointment-card').forEach(function(card) {
            card.remove();
        });
        
        var masterData = calendarData.scheduleByMaster[masterId];
        
        if (!masterData || !masterData.appointments_by_date || !masterData.appointments_by_date[dateKey]) {
            return;
        }
        
        var appointments = masterData.appointments_by_date[dateKey];
        var colors = [
            {from: '#8B5CF6', to: '#6366F1'},
            {from: '#3B82F6', to: '#2563EB'},
            {from: '#10B981', to: '#059669'},
            {from: '#F59E0B', to: '#D97706'},
            {from: '#EF4444', to: '#DC2626'}
        ];
        
        // Перший слот дня
        var dayStartParts = calendarData.timeSlots[0].split(':');
        var dayStartMinutes = parseInt(dayStartParts[0]) * 60 + parseInt(dayStartParts[1]);
        
        appointments.forEach(function(apt, aptIndex) {
            // Парсимо час запису
            var timeParts = apt.time.substring(0, 5).split(':');
            var aptStartMinutes = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);
            var aptEndMinutes = aptStartMinutes + parseInt(apt.duration);
            
            // Offset від початку дня
            var minutesFromDayStart = aptStartMinutes - dayStartMinutes;
            
            // 80px на 30 хвилин
            var pixelsPerMinute = 80 / 30;
            var topPx = minutesFromDayStart * pixelsPerMinute;
            var heightPx = apt.duration * pixelsPerMinute;
            
            // Виявляємо нахлести
            var overlappingCount = 0;
            var positionInOverlap = 0;
            
            appointments.forEach(function(otherApt, otherIndex) {
                var otherTimeParts = otherApt.time.substring(0, 5).split(':');
                var otherStartMinutes = parseInt(otherTimeParts[0]) * 60 + parseInt(otherTimeParts[1]);
                var otherEndMinutes = otherStartMinutes + parseInt(otherApt.duration);
                
                // Перевірка перетину
                if (aptStartMinutes < otherEndMinutes && aptEndMinutes > otherStartMinutes) {
                    overlappingCount++;
                    if (otherIndex < aptIndex) {
                        positionInOverlap++;
                    }
                }
            });

            var widthPercent = overlappingCount > 1 ? (100 / overlappingCount) : 100;
            var leftPercent = positionInOverlap * widthPercent;

            var color = colors[(masterIdx + aptIndex) % colors.length];

            // Обчислюємо час закінчення
            var endHours = Math.floor(aptEndMinutes / 60);
            var endMins = aptEndMinutes % 60;
            var endTimeStr = endHours.toString().padStart(2, '0') + ':' + endMins.toString().padStart(2, '0');

            var card = document.createElement('div');
            card.className = 'appointment-card absolute rounded-lg shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow';
            card.setAttribute('data-appointment-id', apt.id);
            card.style.cssText = 'top: ' + topPx + 'px; ' +
                'height: ' + heightPx + 'px; ' +
                'background: linear-gradient(135deg, ' + color.from + ', ' + color.to + '); ' +
                'z-index: ' + (5 + aptIndex) + '; ' +
                'left: ' + leftPercent + '%; ' +
                'width: calc(' + widthPercent + '% - 4px);';
            card.onclick = function() { showAppointmentDetails(apt.id); };

            var aptTime = apt.time.substring(0, 5);
            var telegramLink = apt.client_telegram
                ? `<a href="https://t.me/${apt.client_telegram}" target="_blank" onclick="event.stopPropagation()" class="ml-1 text-white hover:text-blue-200 flex-shrink-0" title="@${apt.client_telegram}"><i class="fab fa-telegram"></i></a>`
                : '';
            card.innerHTML = `
                <div class="text-white text-xs font-bold mb-1">${aptTime} – ${endTimeStr}${apt.telegram_notification_sent === true ? ' <span class="ml-1">📨</span>' : ''}</div>
                <div class="text-white text-sm font-semibold mb-1 truncate flex items-center">
                    ${apt.client_name}${telegramLink}
                </div>
                <div class="text-white text-xs opacity-90 truncate">${apt.service_name}</div>
            `;

            console.log('Appointment:', apt);
            console.log('Telegram Notification Sent:', apt.telegram_notification_sent);
            if (apt.telegram_notification_sent === true) {
                card.classList.add('border-2', 'border-red-400');
            }

            // Додаємо галочку підтвердження в правому верхньому куті
            var checkmark = document.createElement('div');
            checkmark.className = 'absolute top-1 right-1 w-5 h-5 flex items-center justify-center rounded cursor-pointer transition-all duration-200';
            checkmark.onclick = function(e) {
                e.stopPropagation();
                toggleConfirmationFromCalendar(apt.id);
            };

            if (apt.is_confirmed) {
                checkmark.classList.add('bg-green-500', 'text-white');
            } else {
                checkmark.classList.add('bg-gray-300', 'text-gray-600');
            }

            checkmark.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            `;
            checkmark.setAttribute('data-checkmark', apt.id);

            card.appendChild(checkmark);
            col.appendChild(card);
        });
    });
}

function parseDescriptionWithViberLink(text) {
    console.log('parseDescriptionWithViberLink called with:', text);

    if (!text || text === null || text === undefined) {
        console.log('Text is empty or null, returning empty string');
        return '';
    }

    try {
        // HTML encode text
        var div = document.createElement('div');
        div.textContent = text;
        var encoded = div.innerHTML;

        // Replace line breaks
        encoded = encoded.replace(/\n/g, '<br>').replace(/\r/g, '');

        // Replace Viber links
        encoded = encoded.replace(/viber:\/\/chat\?number=([^&\s<>"']+)/g,
            '<a href="viber://chat?number=$1" class="text-blue-600 hover:text-blue-800 hover:underline"><i class="fab fa-viber mr-1"></i>Viber</a>');

        console.log('parseDescriptionWithViberLink returning:', encoded);
        return encoded;
    } catch (error) {
        console.error('Error in parseDescriptionWithViberLink:', error);
        return '';
    }
}

function showAppointmentDetails(id) {
    console.log('showAppointmentDetails called with id:', id);
    currentAppointmentId = id;

    var modal = document.getElementById('appointmentModal');
    console.log('Modal element:', modal);

    if (!modal) {
        console.error('Modal element not found!');
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    console.log('Modal classes after update:', modal.className);

    document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';

    console.log('Fetching appointment from: /admin/appointments/' + id);

    fetch('/admin/appointments/' + id)
        .then(function(r) {
            console.log('Fetch response status:', r.status);
            if (!r.ok) {
                throw new Error('HTTP ' + r.status + ': ' + r.statusText);
            }
            return r.json();
        })
        .then(function(d) {
            console.log('Appointment data received:', d);

            var statusClasses = {
                'scheduled': 'bg-green-100 text-green-800',
                'completed': 'bg-blue-100 text-blue-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            var sc = statusClasses[d.status];

            var emailHtml = d.client.email
                ? '<div class="text-sm text-gray-600">' + d.client.email + '</div>'
                : '';

            var processedDescription = parseDescriptionWithViberLink(d.client.description);
            var notesHtml = (d.notes && d.notes !== d.client.description) ? d.notes : '';

            var notesContent = processedDescription + (notesHtml && processedDescription ? '<br><br>' : '') + notesHtml;

            var contentHtml =
                '<div class="space-y-3">' +
                    '<div><div class="text-xs text-gray-500 mb-1">Клієнт</div><div class="font-semibold">' + d.client.name + '</div><div class="text-sm text-gray-600">' + d.client.phone + '</div>' + emailHtml + '</div>' +
                    '<div><div class="text-xs text-gray-500 mb-1">Майстер</div><div class="font-medium">' + d.master.name + '</div></div>' +
                    '<div><div class="text-xs text-gray-500 mb-1">Послуга</div><div class="font-medium">' + d.service.name + '</div><div class="text-sm text-gray-600">' + d.service.duration + ' хв</div></div>' +
                    '<div class="flex gap-3"><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Дата</div><div class="font-medium">' + d.appointment_date + '</div></div><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Час</div><div class="font-medium">' + d.appointment_time + '</div></div></div>' +
                    '<div class="flex gap-3"><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Ціна</div><div class="text-lg font-bold text-green-600">' + d.price + '₴</div></div><div class="flex-1"><div class="text-xs text-gray-500 mb-1">Статус</div><span class="inline-block px-2 py-1 text-xs font-semibold rounded-full ' + sc + '">' + d.status_text + '</span></div></div>' +
                    (notesContent ? '<div><div class="text-xs text-gray-500 mb-1">Примітки</div><div class="text-sm bg-gray-50 p-2 rounded">' + notesContent + '</div></div>' : '') +
                '</div>';

            document.getElementById('appointmentContent').innerHTML = contentHtml;
            console.log('Content populated successfully');

            // Оновлюємо стан кнопки швидкого нагадування
            var btn = document.getElementById('quickReminderBtn');
            if (d.telegram_notification_sent === true) {
                btn.disabled = true;
                btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                btn.classList.add('bg-gray-400', 'cursor-not-allowed');
                btn.innerHTML = '<i class="fas fa-check-circle"></i><span>Нагадування вже надіслано</span>';
            } else {
                btn.disabled = false;
                btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                btn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Швидке нагадування "на завтра"</span>';
            }

            // Оновити стан кнопки підтвердження
            var confirmButton = document.getElementById('confirmButton');
            var confirmButtonText = document.getElementById('confirmButtonText');

            if (d.is_confirmed) {
                confirmButton.classList.remove('border-gray-300', 'bg-white', 'text-gray-600');
                confirmButton.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                confirmButtonText.textContent = 'Підтверджено';
            } else {
                confirmButton.classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
                confirmButton.classList.add('border-gray-300', 'bg-white', 'text-gray-600');
                confirmButtonText.textContent = 'Підтвердити';
            }
        })
        .catch(function(error) {
            console.error('Error loading appointment:', error);
            document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8 text-red-500"><div>Помилка завантаження</div><div class="text-sm mt-2">' + error.message + '</div></div>';
        });
}

function sendQuickReminder() {
    if (!currentAppointmentId) {
        showNotification('Помилка: ID запису не знайдено', 'error');
        return;
    }

    var btn = document.getElementById('quickReminderBtn');
    var originalContent = btn.innerHTML;

    // Показуємо лоадер
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Відправка...</span>';

    // Отримуємо CSRF токен
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showNotification('Помилка: CSRF токен не знайдено', 'error');
        btn.disabled = false;
        btn.innerHTML = originalContent;
        return;
    }

    fetch('/admin/appointments/' + currentAppointmentId + '/quick-reminder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        return response.json().then(function(data) {
            return { status: response.status, data: data };
        });
    })
    .then(function(result) {
        if (result.data.success) {
            // Успішно відправлено - робимо кнопку неактивною
            btn.disabled = true;
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-gray-400', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fas fa-check-circle"></i><span>Нагадування вже надіслано</span>';

            showNotification(result.data.message, 'success');
            updateAppointmentCardInCalendar(currentAppointmentId);
            closeModal();
        } else {
            // Помилка - повертаємо оригінальний вміст
            btn.disabled = false;
            btn.innerHTML = originalContent;
            showNotification(result.data.message || 'Помилка відправки', 'error');
        }
    })
    .catch(function(error) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        showNotification('Помилка мережі: ' + error.message, 'error');
    });
}

function updateAppointmentCardInCalendar(appointmentId) {
    // Знаходимо картку запису по data-атрибуту
    var card = document.querySelector('.appointment-card[data-appointment-id="' + appointmentId + '"]');

    if (card) {
        // Додаємо border
        card.classList.add('border-2', 'border-red-400');

        // Додаємо іконку 📨 до часу якщо її ще немає
        var timeDiv = card.querySelector('.text-xs.font-bold');
        if (timeDiv && !timeDiv.innerHTML.includes('📨')) {
            timeDiv.innerHTML += ' <span class="ml-1">📨</span>';
        }
    }

    // Оновлюємо дані в calendarData
    for (var masterId in calendarData.scheduleByMaster) {
        var masterData = calendarData.scheduleByMaster[masterId];
        for (var dateKey in masterData.appointments_by_date) {
            var appointments = masterData.appointments_by_date[dateKey];
            for (var i = 0; i < appointments.length; i++) {
                if (appointments[i].id === appointmentId) {
                    appointments[i].telegram_notification_sent = true;
                }
            }
        }
    }
}

function showNotification(message, type) {
    var bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

    var notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 ' + bgColor + ' text-white px-6 py-3 rounded-lg shadow-lg z-[60] flex items-center gap-2';
    notification.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i><span>' + message + '</span>';

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.transition = 'opacity 0.3s';
        notification.style.opacity = '0';
        setTimeout(function() {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function toggleConfirmation() {
    if (!currentAppointmentId) return;

    var button = document.getElementById('confirmButton');
    var buttonText = document.getElementById('confirmButtonText');

    // Блокуємо кнопку під час запиту
    button.disabled = true;
    button.classList.add('opacity-50', 'cursor-wait');

    fetch('/admin/appointments/' + currentAppointmentId + '/toggle-confirm', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Оновлюємо стан кнопки в попапі
            if (data.is_confirmed) {
                button.classList.remove('border-gray-300', 'bg-white', 'text-gray-600');
                button.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                buttonText.textContent = 'Підтверджено';
            } else {
                button.classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
                button.classList.add('border-gray-300', 'bg-white', 'text-gray-600');
                buttonText.textContent = 'Підтвердити';
            }

            // Оновлюємо галочку на картці в календарі
            updateAppointmentCheckmark(currentAppointmentId, data.is_confirmed);

            // Показуємо toast notification
            showNotification(data.message, 'success');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showNotification('Помилка при оновленні статусу', 'error');
    })
    .finally(function() {
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-wait');
    });
}

function toggleConfirmationFromCalendar(appointmentId) {
    fetch('/admin/appointments/' + appointmentId + '/toggle-confirm', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Оновлюємо галочку на картці
            updateAppointmentCheckmark(appointmentId, data.is_confirmed);

            // Якщо попап відкритий з цим записом - оновити кнопку
            if (currentAppointmentId === appointmentId) {
                var button = document.getElementById('confirmButton');
                var buttonText = document.getElementById('confirmButtonText');

                if (data.is_confirmed) {
                    button.classList.remove('border-gray-300', 'bg-white', 'text-gray-600');
                    button.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                    buttonText.textContent = 'Підтверджено';
                } else {
                    button.classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
                    button.classList.add('border-gray-300', 'bg-white', 'text-gray-600');
                    buttonText.textContent = 'Підтвердити';
                }
            }

            showNotification(data.message, 'success');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showNotification('Помилка при оновленні статусу', 'error');
    });
}

function updateAppointmentCheckmark(appointmentId, isConfirmed) {
    var checkmark = document.querySelector('[data-checkmark="' + appointmentId + '"]');

    if (checkmark) {
        if (isConfirmed) {
            checkmark.classList.remove('bg-gray-300', 'text-gray-600');
            checkmark.classList.add('bg-green-500', 'text-white');
        } else {
            checkmark.classList.remove('bg-green-500', 'text-white');
            checkmark.classList.add('bg-gray-300', 'text-gray-600');
        }
    }
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