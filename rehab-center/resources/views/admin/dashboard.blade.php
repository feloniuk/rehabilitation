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
            <button onclick="openQuickAppointmentModal()" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-1">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Швидкий запис</span>
            </button>
            <button onclick="navigateWeek('previous')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100">
                <i class="fas fa-chevron-left text-gray-600"></i>
            </button>
            <button onclick="navigateToday()" class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                Сьогодні
            </button>
            <button onclick="navigateWeek('next')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100">
                <i class="fas fa-chevron-right text-gray-600"></i>
            </button>
        </div>
    </div>

<!-- 👥 Блок співробітників + таблиця часу -->
<div class="calendar-scroll-wrapper">
    <div class="calendar-table">
        <!-- Шапка з майстрами (sticky top) -->
        <div class="calendar-header">
            <!-- Кут: порожня клітинка над колонкою часу -->
            <div class="time-column-header"></div>
            <!-- Майстри -->
            @foreach($calendar['masters'] as $master)
                <div class="master-header-cell">
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
            @endforeach
        </div>

        <!-- Тіло таблиці -->
        <div class="calendar-body">
            <!-- Колонка часу (sticky left) -->
            <div class="time-column">
                @foreach($calendar['timeSlots'] as $timeSlot)
                    <div class="time-cell">
                        <span class="text-[11px] font-medium text-gray-600">{{ $timeSlot }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Колонки майстрів -->
            @foreach($calendar['masters'] as $masterIndex => $master)
                <div class="master-column"
                     data-master-id="{{ $master->id }}"
                     data-master-index="{{ $masterIndex }}">
                    @php
                        $dateKey = $calendar['weekDates'][0]->format('Y-m-d');
                        $dayAppointments = collect($calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? []);
                    @endphp

                    <!-- Сітка часових слотів -->
                    @foreach($calendar['timeSlots'] as $slotIndex => $timeSlot)
                        <div class="time-slot-cell"
                             data-time-slot="{{ $timeSlot }}"
                             data-slot-index="{{ $slotIndex }}">
                        </div>
                    @endforeach

                    <!-- Записи майстра -->
                    @foreach($dayAppointments as $aptIndex => $apt)
                        @php
                            $startTime = \Carbon\Carbon::parse($apt['time']);
                            $endTime = $startTime->copy()->addMinutes($apt['duration']);

                            $dayStartTime = \Carbon\Carbon::createFromFormat('H:i', $calendar['timeSlots'][0]);
                            $dayStartMinutes = $dayStartTime->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', '00:00'));
                            $aptStartMinutes = $startTime->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', '00:00'));
                            $minutesFromDayStart = $aptStartMinutes - $dayStartMinutes;

                            $pixelsPerMinute = 80 / 30;
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

                            // Поріг для нахлесту: 20 хвилин - достатньо щоб заголовок був видимий
                            $overlapThresholdMinutes = 20;
                            $overlappingCount = 0;
                            $positionInOverlap = 0;

                            foreach($dayAppointments as $otherIndex => $otherApt) {
                                $otherStart = \Carbon\Carbon::parse($otherApt['time']);

                                // Рахуємо конфлікт тільки якщо записи починаються близько одна до одної
                                $startDiff = abs($startTime->diffInMinutes($otherStart));

                                if ($startDiff < $overlapThresholdMinutes) {
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
            <div class="flex items-center gap-2">
                <h3 class="font-semibold">Деталі запису</h3>
                <button id="rescheduleAppointmentBtn"
                        onclick="openRescheduleModal()"
                        class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-blue-100 text-blue-600 transition-colors"
                        title="Перенести запис">
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
                <button id="repeatAppointmentBtn"
                        onclick="openRepeatAppointmentModal()"
                        class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-purple-100 text-purple-600 transition-colors"
                        title="Повторний запис">
                    <i class="fas fa-redo-alt text-sm"></i>
                </button>
                <button id="cancelAppointmentBtn"
                        onclick="openCancelConfirmation()"
                        class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-red-100 text-red-600 transition-colors"
                        title="Скасувати запис">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
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
                <button id="quickReminderBtn" onclick="sendQuickReminder()" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2" style="flex: 0 0 calc(90% - 4px);">
                    <i class="fas fa-paper-plane"></i>
                    <span>Швидке нагадування "на завтра"</span>
                </button>
                <button id="copyReminderBtn" onclick="copyReminderText()" class="bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center" style="flex: 0 0 10%;" title="Копіювати текст">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <div class="flex gap-2 mt-2">
                <button onclick="closeModal()" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">
                    Закрити
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модалка повторного запису -->
<div id="repeatAppointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <div class="flex items-center gap-2">
                <i class="fas fa-redo-alt text-purple-600"></i>
                <h3 class="font-semibold">Повторний запис</h3>
            </div>
            <button onclick="closeRepeatModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-4 space-y-4">
            <!-- Інформація про запис -->
            <div id="repeatAppointmentInfo" class="bg-gray-50 rounded-lg p-3 space-y-2">
                <!-- Заповнюється JavaScript -->
            </div>

            <!-- Вибір дати -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Дата</label>
                <input type="date"
                       id="repeatDate"
                       onchange="loadRepeatSlots()"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                       min="{{ date('Y-m-d') }}">
            </div>

            <!-- Вибір часу -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Доступний час</label>
                <div id="repeatSlotsContainer" class="min-h-[60px]">
                    <div class="text-sm text-gray-500 text-center py-4">
                        Оберіть дату для перегляду доступних слотів
                    </div>
                </div>

                <!-- Чекбокс кастомного часу -->
                <div id="customTimeCheckboxContainer" class="hidden mt-3">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                               id="customTimeCheckbox"
                               onchange="toggleCustomTime()"
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-600">
                            <i class="fas fa-edit text-purple-500 mr-1"></i>
                            Кастомний час
                        </span>
                    </label>
                </div>

                <!-- Інпути кастомного часу -->
                <div id="customTimeInputs" class="hidden mt-3">
                    <div class="flex gap-2 items-center">
                        <div class="flex-1">
                            <input type="number"
                                   id="repeatHour"
                                   min="0" max="23" step="1"
                                   placeholder="00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-center">
                        </div>
                        <div class="text-lg font-semibold text-gray-500">:</div>
                        <div class="flex-1">
                            <input type="number"
                                   id="repeatMinute"
                                   min="0" max="59" step="1"
                                   placeholder="00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-center">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Години: 0-23, Хвилини: 0-59
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 border-t flex gap-2">
            <button id="createRepeatBtn"
                    onclick="createRepeatAppointment()"
                    disabled
                    class="flex-1 bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Створити запис</span>
            </button>
            <button onclick="closeRepeatModal()" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">
                Скасувати
            </button>
        </div>
    </div>
</div>

<!-- Модалка швидкого запису -->
<div id="quickAppointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-lg max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <div class="flex items-center gap-2">
                <i class="fas fa-plus-circle text-green-600"></i>
                <h3 class="font-semibold">Швидкий запис</h3>
            </div>
            <button onclick="closeQuickAppointmentModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="quickAppointmentForm" class="p-4 space-y-4">
            <!-- Майстер -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-user-md text-blue-500 mr-1"></i>Майстер *
                </label>
                <select id="qa_master_id" name="master_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Оберіть майстра</option>
                    @foreach($calendar['masters'] as $master)
                        <option value="{{ $master->id }}">{{ $master->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Послуга -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-spa text-green-500 mr-1"></i>Послуга *
                </label>
                <select id="qa_service_id" name="service_id" required disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Спочатку оберіть майстра</option>
                </select>
            </div>

            <!-- Дата та час -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar text-purple-500 mr-1"></i>Дата *
                    </label>
                    <input type="date" id="qa_date" name="appointment_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-clock text-orange-500 mr-1"></i>Час *
                    </label>
                    <div class="flex gap-1 items-center">
                        <input type="number" id="qa_hour" min="0" max="23" placeholder="09" class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-center">
                        <span class="text-lg font-bold text-gray-400">:</span>
                        <input type="number" id="qa_minute" min="0" max="59" placeholder="00" class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-center">
                    </div>
                </div>
            </div>

            <!-- Клієнт -->
            <div class="border-t pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user text-blue-600 mr-1"></i>Клієнт
                </label>
                <div class="flex gap-4 mb-3">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="qa_client_type" value="existing" checked class="mr-2">
                        <span class="text-sm">Існуючий</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="qa_client_type" value="new" class="mr-2">
                        <span class="text-sm">Новий</span>
                    </label>
                </div>

                <!-- Пошук існуючого клієнта -->
                <div id="qa_existing_client_block">
                    <input type="text" id="qa_client_search" placeholder="Пошук по імені або телефону..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 mb-2">
                    <div id="qa_client_results" class="border border-gray-200 rounded-lg max-h-40 overflow-y-auto bg-gray-50">
                        <div class="p-4 text-center text-gray-500 text-sm">
                            <i class="fas fa-search text-gray-400 mr-1"></i>Введіть мін. 2 символи
                        </div>
                    </div>
                    <input type="hidden" id="qa_existing_client" name="existing_client">
                </div>

                <!-- Новий клієнт -->
                <div id="qa_new_client_block" class="hidden space-y-3">
                    <div>
                        <input type="text" id="qa_new_client_name" name="new_client_name" placeholder="Ім'я клієнта *" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <input type="tel" id="qa_new_client_phone" name="new_client_phone" placeholder="Телефон *" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Помилки -->
            <div id="qa_errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-sm"></div>
        </form>

        <div class="p-4 border-t flex gap-2">
            <button id="qa_submit_btn" onclick="submitQuickAppointment()" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                <span>Створити запис</span>
            </button>
            <button onclick="closeQuickAppointmentModal()" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">
                Скасувати
            </button>
        </div>
    </div>
</div>

<!-- Модалка скасування запису -->
<div id="cancelAppointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-lg max-w-sm w-full">
        <div class="flex justify-between items-center p-4 border-b">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                <h3 class="font-semibold">Скасувати запис?</h3>
            </div>
            <button onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4">
            <p class="text-gray-700 mb-2">Ви впевнені, що хочете скасувати цю запис?</p>
            <p id="cancelAppointmentInfo" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg mb-4"></p>
        </div>
        <div class="p-4 border-t flex gap-2">
            <button onclick="cancelAppointment()" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors font-medium">
                <i class="fas fa-check mr-2"></i>Скасувати
            </button>
            <button onclick="closeCancelModal()" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors font-medium">
                Ні, залишити
            </button>
        </div>
    </div>
</div>

<!-- Модалка переносу запису -->
<div id="rescheduleAppointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[70] p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <div class="flex items-center gap-2">
                <i class="fas fa-arrow-right text-blue-600"></i>
                <h3 class="font-semibold">Перенести запис</h3>
            </div>
            <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 space-y-4">
            <!-- Інформація про запис -->
            <div id="rescheduleAppointmentInfo" class="space-y-2 text-sm bg-blue-50 p-3 rounded-lg border border-blue-200"></div>

            <!-- Дата та час -->
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar text-blue-500 mr-1"></i>Нова дата *
                    </label>
                    <input type="date" id="rescheduleDate" onchange="loadRescheduleSlots()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Доступні слоти -->
                <div id="rescheduleSlotsContainer" class="text-sm text-gray-500 text-center py-4">
                    Оберіть дату для перегляду доступних слотів
                </div>

                <!-- Чекбокс кастомного часу -->
                <div id="rescheduleCustomTimeCheckboxContainer" class="hidden mt-3">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                               id="rescheduleCustomTimeCheckbox"
                               onchange="toggleRescheduleCustomTime()"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">
                            <i class="fas fa-edit text-blue-500 mr-1"></i>
                            Кастомний час
                        </span>
                    </label>
                </div>

                <!-- Кастомний час інпути -->
                <div id="rescheduleCustomTimeInputs" class="hidden mt-3">
                    <div class="flex gap-1 items-center">
                        <input type="number" id="rescheduleHour" min="0" max="23" placeholder="09" class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-center">
                        <span class="text-lg font-bold text-gray-400">:</span>
                        <input type="number" id="rescheduleMinute" min="0" max="59" placeholder="00" class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-center">
                    </div>
                </div>
            </div>
        </div>
        <div class="p-4 border-t flex gap-2">
            <button id="confirmRescheduleBtn" onclick="rescheduleAppointment()" disabled class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-check mr-2"></i>Перенести
            </button>
            <button onclick="closeRescheduleModal()" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors font-medium">
                Скасувати
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Фікс ширини інпута дати в модалці на мобільних */
#repeatDate {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Контейнер модалки - обмеження overflow */
#repeatAppointmentModal .p-4 {
    overflow: hidden;
}

/* Заборона горизонтальної прокрутки сторінки */
#calendar-container {
    max-width: 100%;
    overflow: hidden;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Основний контейнер календаря */
.calendar-scroll-wrapper {
    overflow: auto;
    max-height: calc(100vh - 280px);
    min-height: 200px;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #fff;
    width: 100%;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
}

/* Для landscape на мобільних */
@media (max-height: 500px) {
    .calendar-scroll-wrapper {
        max-height: calc(100vh - 160px);
        min-height: 150px;
    }
}

/* Таблиця календаря */
.calendar-table {
    display: flex;
    flex-direction: column;
    min-width: max-content;
}

/* Шапка з майстрами */
.calendar-header {
    display: flex;
    position: sticky;
    top: 0;
    z-index: 20;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.time-column-header {
    flex-shrink: 0;
    width: 56px;
    min-width: 56px;
    background: #f3f4f6;
    border-right: 1px solid #e5e7eb;
    position: sticky;
    left: 0;
    z-index: 25;
}

.master-header-cell {
    flex: 1;
    min-width: 140px;
    padding: 12px 8px;
    text-align: center;
    border-right: 1px solid #e5e7eb;
    background: #f9fafb;
}

.master-header-cell:last-child {
    border-right: none;
}

/* Тіло таблиці */
.calendar-body {
    display: flex;
    position: relative;
}

/* Колонка часу (sticky left) */
.time-column {
    flex-shrink: 0;
    width: 56px;
    min-width: 56px;
    background: #f9fafb;
    border-right: 1px solid #e5e7eb;
    position: sticky;
    left: 0;
    z-index: 15;
}

.time-cell {
    height: 80px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 4px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

/* Колонки майстрів */
.master-column {
    flex: 1;
    min-width: 140px;
    position: relative;
    border-right: 1px solid #e5e7eb;
}

.master-column:last-child {
    border-right: none;
}

.time-slot-cell {
    height: 80px;
    border-bottom: 1px dashed #e5e7eb;
    position: relative;
    background: #fff;
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.time-slot-cell:hover:not(.non-working) {
    background-color: #f0fdf4;
}

.time-slot-cell:active:not(.non-working) {
    background-color: #dcfce7;
}

.time-slot-cell.non-working {
    cursor: default;
}

/* Ефект "пульсу" при кліку */
.time-slot-cell.clicked {
    animation: slotPulse 0.3s ease;
}

@keyframes slotPulse {
    0% { background-color: #dcfce7; }
    50% { background-color: #bbf7d0; }
    100% { background-color: #f0fdf4; }
}

/* Нерабочий час (до початку та після закінчення робочого дня) */
.time-slot-cell.non-working {
    background: repeating-linear-gradient(
        -45deg,
        transparent,
        transparent 10px,
        #e5e7eb 10px,
        #e5e7eb 12px
    );
}

/* Картки записів */
.appointment-card {
    overflow: hidden;
}

/* Мобільна адаптація */
@media (max-width: 640px) {
    .master-header-cell {
        min-width: 120px;
        padding: 8px 4px;
    }

    .master-column {
        min-width: 120px;
    }

    .time-column-header,
    .time-column {
        width: 48px;
        min-width: 48px;
    }
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
            specialty: "{{ addslashes($master->specialty ?? '') }}",
            work_schedule: @json($master->work_schedule ?? [])
        }@if(!$loop->last),@endif
        @endforeach
    ],
    timeSlots: @json($calendar['timeSlots']),
    todayIndex: {{ $calendar['todayIndex'] }},
    selectDateRoute: "{{ route('admin.select-date') }}"
};

var currentDayIndex = {{ $selectedDateIndex ?? 0 }};
var currentAppointmentId = null;
var currentAppointmentData = null;
var currentWeekOffset = 0;

document.addEventListener('DOMContentLoaded', function() {
    selectDate(currentDayIndex);
    scrollToCurrentHour();
    initTimeSlotClickHandlers();
});

function navigateWeek(direction) {
    var loadingBtn = event.target.closest('button');
    loadingBtn.disabled = true;
    loadingBtn.style.opacity = '0.5';

    fetch("{{ route('admin.load-calendar') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            direction: direction,
            week_offset: currentWeekOffset
        })
    })
    .then(function(response) {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            currentWeekOffset = data.weekOffset;
            updateCalendar(data.calendar, data.selectedDateIndex);
            scrollToCurrentHour();
        }
    })
    .catch(function(error) {
        console.error('Error loading calendar:', error);
        showNotification('Помилка при завантаженні календаря', 'error');
    })
    .finally(function() {
        loadingBtn.disabled = false;
        loadingBtn.style.opacity = '1';
    });
}

function navigateToday() {
    var loadingBtn = event.target.closest('button');
    loadingBtn.disabled = true;
    loadingBtn.style.opacity = '0.5';

    fetch("{{ route('admin.load-calendar') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            direction: 'today',
            week_offset: currentWeekOffset
        })
    })
    .then(function(response) {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            currentWeekOffset = data.weekOffset;
            updateCalendar(data.calendar, data.selectedDateIndex);
            scrollToCurrentHour();
        }
    })
    .catch(function(error) {
        console.error('Error loading calendar:', error);
        showNotification('Помилка при завантаженні календаря', 'error');
    })
    .finally(function() {
        loadingBtn.disabled = false;
        loadingBtn.style.opacity = '1';
    });
}

function updateCalendar(calendar, selectedDateIndex) {
    // Оновлюємо дані calendarData
    calendarData.scheduleByMaster = calendar.scheduleByMaster;
    calendarData.weekDates = calendar.weekDates; // weekDates вже в форматі Y-m-d
    calendarData.todayIndex = calendar.todayIndex;

    // Оновлюємо masters якщо вони передані
    if (calendar.masters) {
        calendarData.masters = calendar.masters;
    }

    // Оновлюємо timeSlots якщо вони змінились (динамічні межі часу)
    if (calendar.timeSlots && JSON.stringify(calendar.timeSlots) !== JSON.stringify(calendarData.timeSlots)) {
        calendarData.timeSlots = calendar.timeSlots;
        rebuildTimeColumn();
    }

    // Оновлюємо заголовок
    var firstDateStr = calendarData.weekDates[0];
    var firstDate = new Date(firstDateStr + 'T00:00:00');
    document.getElementById('current-date').textContent =
        firstDate.getDate().toString().padStart(2, '0') + '.' +
        (firstDate.getMonth() + 1).toString().padStart(2, '0');

    // Оновлюємо кнопки дат у нижній панелі
    updateDateButtons(calendarData.weekDates, calendar.todayIndex, selectedDateIndex);

    // Перезавантажуємо календар з новим днем
    currentDayIndex = selectedDateIndex;
    reloadTimeline(selectedDateIndex);
    // Третій параметр true = пропустити AJAX збереження, бо loadCalendar вже зберіг дату
    selectDate(selectedDateIndex, true);
}

function updateDateButtons(weekDates, todayIndex, selectedIndex) {
    var dateButtons = document.querySelectorAll('.date-btn');
    dateButtons.forEach(function(btn) {
        btn.remove();
    });

    var datePanel = document.querySelector('.flex.overflow-x-auto.hide-scrollbar');
    if (!datePanel) return;

    var dayNames = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'нд'];

    weekDates.forEach(function(dateStr, index) {
        var date = new Date(dateStr + 'T00:00:00');
        var dayIndex = date.getDay();
        var dayName = dayNames[dayIndex === 0 ? 6 : dayIndex - 1];

        var isSelected = index === selectedIndex;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'date-btn flex-1 min-w-[60px] py-3 text-center border-r last:border-r-0 transition-colors ' +
            (isSelected ? 'bg-purple-500 text-white active' : 'hover:bg-purple-200');
        btn.setAttribute('data-date-index', index);
        btn.onclick = function() { selectDate(index); };

        var smallText = document.createElement('div');
        smallText.className = 'text-[10px] font-medium ' + (isSelected ? 'text-purple-100' : 'text-gray-500');
        smallText.textContent = dayName.toUpperCase();

        var dayNum = document.createElement('div');
        dayNum.className = 'text-lg font-bold mt-1';
        dayNum.textContent = date.getDate().toString().padStart(2, '0');

        btn.appendChild(smallText);
        btn.appendChild(dayNum);
        datePanel.appendChild(btn);
    });
}

function rebuildTimeColumn() {
    // Перебудовуємо колонку часу
    var timeColumn = document.querySelector('.time-column');
    if (timeColumn) {
        timeColumn.innerHTML = '';
        calendarData.timeSlots.forEach(function(timeSlot) {
            var cell = document.createElement('div');
            cell.className = 'time-cell';
            cell.innerHTML = '<span class="text-[11px] font-medium text-gray-600">' + timeSlot + '</span>';
            timeColumn.appendChild(cell);
        });
    }

    // Перебудовуємо слоти в колонках мастерів
    var masterColumns = document.querySelectorAll('.master-column[data-master-id]');
    masterColumns.forEach(function(col) {
        // Видаляємо всі старі слоти
        col.querySelectorAll('.time-slot-cell').forEach(function(cell) {
            cell.remove();
        });
        col.querySelectorAll('.appointment-card').forEach(function(card) {
            card.remove();
        });

        // Створюємо нові слоти
        calendarData.timeSlots.forEach(function(timeSlot, slotIndex) {
            var cell = document.createElement('div');
            cell.className = 'time-slot-cell';
            cell.setAttribute('data-time-slot', timeSlot);
            cell.setAttribute('data-slot-index', slotIndex);
            col.appendChild(cell);
        });
    });
}

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
    var calendarWrapper = document.querySelector('.calendar-scroll-wrapper');
    if (calendarWrapper) {
        calendarWrapper.scrollTop = scrollPosition;
    }
}

function selectDate(index, skipSave) {
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

    var date = new Date(calendarData.weekDates[index] + 'T00:00:00');
    document.getElementById('current-date').textContent =
        date.getDate().toString().padStart(2, '0') + '.' +
        (date.getMonth() + 1).toString().padStart(2, '0');

    reloadTimeline(index);

    // Зберігаємо вибрану дату в сесію через AJAX тільки якщо це не було пропущено
    if (!skipSave) {
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
}

function reloadTimeline(dayIndex) {
    var dateKey = calendarData.weekDates[dayIndex];
    var masterColumns = document.querySelectorAll('.master-column[data-master-id]');

    // Отримуємо день тижня для вибраної дати
    var selectedDate = new Date(dateKey + 'T00:00:00');
    var dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, etc.
    var dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    var dayName = dayNames[dayOfWeek];

    masterColumns.forEach(function(col) {
        var masterId = parseInt(col.dataset.masterId);
        var masterIdx = parseInt(col.dataset.masterIndex);

        // Видаляємо всі старі картки
        col.querySelectorAll('.appointment-card').forEach(function(card) {
            card.remove();
        });

        var masterData = calendarData.scheduleByMaster[masterId];
        var appointments = (masterData && masterData.appointments_by_date && masterData.appointments_by_date[dateKey])
            ? masterData.appointments_by_date[dateKey]
            : [];

        // Знаходимо графік роботи мастера для вибраного дня
        var master = calendarData.masters.find(function(m) { return m.id === masterId; });
        var workSchedule = master && master.work_schedule ? master.work_schedule[dayName] : null;

        // Визначаємо робочі години мастера
        var workStartMinutes = null;
        var workEndMinutes = null;
        var isWorkingDay = false;

        if (workSchedule && workSchedule.is_working) {
            isWorkingDay = true;
            if (workSchedule.start) {
                var startParts = workSchedule.start.split(':');
                workStartMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            }
            if (workSchedule.end) {
                var endParts = workSchedule.end.split(':');
                workEndMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
            }
        }

        // Позначаємо нерабочі слоти на основі графіку мастера
        var timeSlotCells = col.querySelectorAll('.time-slot-cell');
        timeSlotCells.forEach(function(cell, slotIndex) {
            cell.classList.remove('non-working');

            var slotTime = calendarData.timeSlots[slotIndex];
            var slotParts = slotTime.split(':');
            var slotMinutes = parseInt(slotParts[0]) * 60 + parseInt(slotParts[1]);
            var slotEndMinutes = slotMinutes + 30; // Кожен слот 30 хвилин

            if (!isWorkingDay) {
                // Мастер не працює в цей день - всі слоти нерабочі
                cell.classList.add('non-working');
            } else if (workStartMinutes !== null && workEndMinutes !== null) {
                // Слот повністю за межами робочого часу
                if (slotEndMinutes <= workStartMinutes || slotMinutes >= workEndMinutes) {
                    cell.classList.add('non-working');
                }
            }
        });

        if (appointments.length === 0) {
            return;
        }
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
            
            // Виявляємо нахлести - тільки для записів які починаються близько одна до одної
            // Поріг: 20 хвилин - достатньо щоб заголовок попередньої картки був видимий
            var OVERLAP_THRESHOLD_MINUTES = 20;
            var overlappingCount = 0;
            var positionInOverlap = 0;

            appointments.forEach(function(otherApt, otherIndex) {
                var otherTimeParts = otherApt.time.substring(0, 5).split(':');
                var otherStartMinutes = parseInt(otherTimeParts[0]) * 60 + parseInt(otherTimeParts[1]);

                // Рахуємо конфлікт тільки якщо записи починаються близько одна до одної
                // АБО якщо поточна картка починається в межах порогу від початку іншої
                var startDiff = Math.abs(aptStartMinutes - otherStartMinutes);

                if (startDiff < OVERLAP_THRESHOLD_MINUTES) {
                    // Записи починаються майже одночасно - потрібно ділити ширину
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

            // Зберігаємо дані для інших функцій (скасування, перенесення)
            currentAppointmentData = d;

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

function copyReminderText() {
    if (!currentAppointmentId) {
        showNotification('Помилка: ID запису не знайдено', 'error');
        return;
    }

    var btn = document.getElementById('copyReminderBtn');
    var originalContent = btn.innerHTML;

    // Показуємо лоадер
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/admin/appointments/' + currentAppointmentId + '/reminder-text', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        if (result.success) {
            // Копіюємо текст в буфер обміну
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(result.text).then(function() {
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    showNotification('Текст скопійовано!', 'success');
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }, 1500);
                }).catch(function(err) {
                    // Fallback для старих браузерів
                    fallbackCopyText(result.text);
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    showNotification('Текст скопійовано!', 'success');
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }, 1500);
                });
            } else {
                // Fallback для старих браузерів та мобільних
                fallbackCopyText(result.text);
                btn.innerHTML = '<i class="fas fa-check"></i>';
                showNotification('Текст скопійовано!', 'success');
                setTimeout(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }, 1500);
            }
        } else {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            showNotification(result.message || 'Помилка отримання тексту', 'error');
        }
    })
    .catch(function(error) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        showNotification('Помилка мережі: ' + error.message, 'error');
    });
}

function fallbackCopyText(text) {
    // Створюємо тимчасовий textarea
    var textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    textArea.style.top = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('Fallback copy failed:', err);
    }

    document.body.removeChild(textArea);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickAppointmentModal();
        closeRepeatModal();
        closeModal();
    }
});

// ============================================
// Повторний запис - функції
// ============================================

var currentRepeatAppointmentData = null;
var selectedRepeatSlot = null;
var isCustomTimeMode = false;

function openRepeatAppointmentModal() {
    if (!currentAppointmentId) {
        showNotification('Помилка: ID запису не знайдено', 'error');
        return;
    }

    // Завантажуємо дані запису для повторення
    fetch('/admin/appointments/' + currentAppointmentId)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(d) {
            currentRepeatAppointmentData = d;

            // Заповнюємо інформацію про запис
            var infoHtml =
                '<div class="flex items-center gap-2">' +
                    '<i class="fas fa-user text-gray-400 text-sm"></i>' +
                    '<span class="font-medium">' + d.client.name + '</span>' +
                '</div>' +
                '<div class="flex items-center gap-2">' +
                    '<i class="fas fa-user-md text-gray-400 text-sm"></i>' +
                    '<span class="text-sm text-gray-600">' + d.master.name + '</span>' +
                '</div>' +
                '<div class="flex items-center gap-2">' +
                    '<i class="fas fa-concierge-bell text-gray-400 text-sm"></i>' +
                    '<span class="text-sm text-gray-600">' + d.service.name + ' (' + d.service.duration + ' хв)</span>' +
                '</div>' +
                '<div class="flex items-center gap-2">' +
                    '<i class="fas fa-money-bill text-gray-400 text-sm"></i>' +
                    '<span class="text-sm font-semibold text-green-600">' + d.price + '₴</span>' +
                '</div>';

            document.getElementById('repeatAppointmentInfo').innerHTML = infoHtml;

            // Скидаємо форму
            document.getElementById('repeatDate').value = '';
            document.getElementById('repeatSlotsContainer').innerHTML =
                '<div class="text-sm text-gray-500 text-center py-4">Оберіть дату для перегляду доступних слотів</div>';
            document.getElementById('createRepeatBtn').disabled = true;
            selectedRepeatSlot = null;

            // Скидаємо кастомний час
            resetCustomTimeMode();

            // Встановлюємо мінімальну дату (завтра)
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('repeatDate').min = tomorrow.toISOString().split('T')[0];

            // Показуємо модалку
            var modal = document.getElementById('repeatAppointmentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        })
        .catch(function(error) {
            console.error('Error loading appointment:', error);
            showNotification('Помилка завантаження даних запису', 'error');
        });
}

function closeRepeatModal() {
    var modal = document.getElementById('repeatAppointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentRepeatAppointmentData = null;
    selectedRepeatSlot = null;
    resetCustomTimeMode();
}

function resetCustomTimeMode() {
    isCustomTimeMode = false;
    var checkbox = document.getElementById('customTimeCheckbox');
    if (checkbox) checkbox.checked = false;
    document.getElementById('customTimeCheckboxContainer').classList.add('hidden');
    document.getElementById('customTimeInputs').classList.add('hidden');
    document.getElementById('repeatHour').value = '';
    document.getElementById('repeatMinute').value = '';
}

function loadRepeatSlots() {
    var date = document.getElementById('repeatDate').value;

    if (!date || !currentRepeatAppointmentData) {
        return;
    }

    var container = document.getElementById('repeatSlotsContainer');
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-xl text-gray-400"></i></div>';
    document.getElementById('createRepeatBtn').disabled = true;
    selectedRepeatSlot = null;

    // Скидаємо кастомний час при зміні дати
    resetCustomTimeMode();

    // Використовуємо існуючий API для отримання слотів
    var masterId = currentRepeatAppointmentData.master.id;
    var serviceId = currentRepeatAppointmentData.service.id;

    fetch('/masters/' + masterId + '/available-slots/' + date + '/' + serviceId)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(slots) {
            // Показуємо чекбокс кастомного часу
            document.getElementById('customTimeCheckboxContainer').classList.remove('hidden');

            if (slots.length === 0) {
                container.innerHTML =
                    '<div class="text-center py-4">' +
                        '<i class="fas fa-calendar-times text-gray-400 text-2xl mb-2"></i>' +
                        '<div class="text-sm text-gray-500">Немає доступних слотів на цю дату</div>' +
                    '</div>';
                return;
            }

            var slotsHtml = '<div id="slotsGrid" class="grid grid-cols-4 gap-2">';
            slots.forEach(function(slot) {
                slotsHtml +=
                    '<button type="button" ' +
                           'onclick="selectRepeatSlot(\'' + slot + '\', this)" ' +
                           'class="repeat-slot-btn px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors">' +
                        slot +
                    '</button>';
            });
            slotsHtml += '</div>';

            container.innerHTML = slotsHtml;
        })
        .catch(function(error) {
            console.error('Error loading slots:', error);
            container.innerHTML =
                '<div class="text-center py-4 text-red-500">' +
                    '<i class="fas fa-exclamation-circle text-2xl mb-2"></i>' +
                    '<div class="text-sm">Помилка завантаження слотів</div>' +
                '</div>';
            // Все одно показуємо чекбокс для кастомного часу
            document.getElementById('customTimeCheckboxContainer').classList.remove('hidden');
        });
}

function toggleCustomTime() {
    var checkbox = document.getElementById('customTimeCheckbox');
    var slotsContainer = document.getElementById('repeatSlotsContainer');
    var customInputs = document.getElementById('customTimeInputs');
    var slotsGrid = document.getElementById('slotsGrid');

    isCustomTimeMode = checkbox.checked;

    if (isCustomTimeMode) {
        // Ховаємо слоти, показуємо інпути
        if (slotsGrid) slotsGrid.classList.add('hidden');
        customInputs.classList.remove('hidden');

        // Скидаємо вибраний слот
        selectedRepeatSlot = null;
        document.querySelectorAll('.repeat-slot-btn').forEach(function(btn) {
            btn.classList.remove('bg-purple-600', 'text-white', 'border-purple-600');
            btn.classList.add('border-gray-300');
        });

        // Активуємо кнопку якщо введено коректний час
        updateCreateButtonState();

        // Додаємо обробники для інпутів часу
        setupCustomTimeInputs();
    } else {
        // Показуємо слоти, ховаємо інпути
        if (slotsGrid) slotsGrid.classList.remove('hidden');
        customInputs.classList.add('hidden');

        // Скидаємо інпути
        document.getElementById('repeatHour').value = '';
        document.getElementById('repeatMinute').value = '';

        // Деактивуємо кнопку (поки не вибраний слот)
        document.getElementById('createRepeatBtn').disabled = true;
    }
}

function setupCustomTimeInputs() {
    var hourInput = document.getElementById('repeatHour');
    var minuteInput = document.getElementById('repeatMinute');

    // Видаляємо старі обробники щоб не дублювати
    hourInput.onblur = formatRepeatTimeInputs;
    hourInput.onchange = formatRepeatTimeInputs;
    hourInput.oninput = updateCreateButtonState;

    minuteInput.onblur = formatRepeatTimeInputs;
    minuteInput.onchange = formatRepeatTimeInputs;
    minuteInput.oninput = updateCreateButtonState;
}

function formatRepeatTimeInputs() {
    var hourInput = document.getElementById('repeatHour');
    var minuteInput = document.getElementById('repeatMinute');

    var hour = parseInt(hourInput.value);
    var minute = parseInt(minuteInput.value);

    // Валідація години
    if (!isNaN(hour)) {
        hour = Math.max(0, Math.min(23, hour));
        hourInput.value = String(hour).padStart(2, '0');
    }

    // Валідація хвилин
    if (!isNaN(minute)) {
        minute = Math.max(0, Math.min(59, minute));
        minuteInput.value = String(minute).padStart(2, '0');
    }

    updateCreateButtonState();
}

function updateCreateButtonState() {
    if (!isCustomTimeMode) return;

    var hourInput = document.getElementById('repeatHour');
    var minuteInput = document.getElementById('repeatMinute');
    var btn = document.getElementById('createRepeatBtn');

    var hour = parseInt(hourInput.value);
    var minute = parseInt(minuteInput.value);

    // Перевіряємо чи введено коректний час
    var isValid = !isNaN(hour) && !isNaN(minute) &&
                  hour >= 0 && hour <= 23 &&
                  minute >= 0 && minute <= 59;

    btn.disabled = !isValid;
}

function getSelectedTime() {
    if (isCustomTimeMode) {
        var hourInput = document.getElementById('repeatHour');
        var minuteInput = document.getElementById('repeatMinute');

        var hour = parseInt(hourInput.value);
        var minute = parseInt(minuteInput.value);

        if (!isNaN(hour) && !isNaN(minute)) {
            return String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
        }
        return null;
    }
    return selectedRepeatSlot;
}

function selectRepeatSlot(slot, button) {
    // Знімаємо виділення з інших кнопок
    document.querySelectorAll('.repeat-slot-btn').forEach(function(btn) {
        btn.classList.remove('bg-purple-600', 'text-white', 'border-purple-600');
        btn.classList.add('border-gray-300');
    });

    // Виділяємо обрану кнопку
    button.classList.remove('border-gray-300');
    button.classList.add('bg-purple-600', 'text-white', 'border-purple-600');

    selectedRepeatSlot = slot;

    // Скидаємо режим кастомного часу
    if (isCustomTimeMode) {
        document.getElementById('customTimeCheckbox').checked = false;
        toggleCustomTime();
    }

    document.getElementById('createRepeatBtn').disabled = false;
}

function createRepeatAppointment() {
    var selectedTime = getSelectedTime();

    if (!currentRepeatAppointmentData || !selectedTime) {
        showNotification('Оберіть дату та час', 'error');
        return;
    }

    var date = document.getElementById('repeatDate').value;
    if (!date) {
        showNotification('Оберіть дату', 'error');
        return;
    }

    var btn = document.getElementById('createRepeatBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Створення...</span>';

    var data = {
        original_appointment_id: currentAppointmentId,
        appointment_date: date,
        appointment_time: selectedTime
    };

    fetch('/admin/appointments/repeat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        return response.json().then(function(d) {
            return { status: response.status, data: d };
        });
    })
    .then(function(result) {
        if (result.data.success) {
            showNotification(result.data.message || 'Повторний запис створено', 'success');

            // Додаємо новий запис до calendarData якщо він на поточному тижні
            if (result.data.appointment) {
                addNewAppointmentToCalendar(result.data.appointment);
            }

            // Закриваємо обидві модалки
            closeRepeatModal();
            closeModal();
        } else {
            showNotification(result.data.message || 'Помилка створення запису', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i><span>Створити запис</span>';
        }
    })
    .catch(function(error) {
        console.error('Error creating repeat appointment:', error);
        showNotification('Помилка мережі: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i><span>Створити запис</span>';
    });
}

function addNewAppointmentToCalendar(appointment) {
    // Перевіряємо чи запис на поточному тижні
    var appointmentDate = appointment.appointment_date;
    var weekDates = calendarData.weekDates;

    var dateIndex = -1;
    for (var i = 0; i < weekDates.length; i++) {
        if (weekDates[i] === appointmentDate) {
            dateIndex = i;
            break;
        }
    }

    if (dateIndex === -1) {
        // Запис не на поточному тижні - просто показуємо повідомлення
        return;
    }

    // Додаємо запис до calendarData
    var masterId = appointment.master_id;
    if (!calendarData.scheduleByMaster[masterId]) {
        return;
    }

    if (!calendarData.scheduleByMaster[masterId].appointments_by_date[appointmentDate]) {
        calendarData.scheduleByMaster[masterId].appointments_by_date[appointmentDate] = [];
    }

    calendarData.scheduleByMaster[masterId].appointments_by_date[appointmentDate].push({
        id: appointment.id,
        time: appointment.appointment_time,
        duration: appointment.duration,
        client_name: appointment.client_name,
        client_telegram: appointment.client_telegram,
        service_name: appointment.service_name,
        price: appointment.price,
        status: appointment.status,
        telegram_notification_sent: appointment.telegram_notification_sent,
        is_confirmed: appointment.is_confirmed || false
    });

    // Перемальовуємо календар якщо обраний цей день
    if (currentDayIndex === dateIndex) {
        reloadTimeline(dateIndex);
    }
}

// ============================================
// Скасування записи - функції
// ============================================

function openCancelConfirmation() {
    if (!currentAppointmentId) {
        showNotification('Помилка: ID запису не знайдено', 'error');
        return;
    }

    const modal = document.getElementById('cancelAppointmentModal');
    const infoDiv = document.getElementById('cancelAppointmentInfo');

    // Показуємо інформацію про запис
    infoDiv.innerHTML = `
        <div><strong>${currentAppointmentData.client_name}</strong></div>
        <div class="text-xs text-gray-600">${currentAppointmentData.appointment_date} о ${currentAppointmentData.appointment_time}</div>
        <div class="text-xs text-gray-600">${currentAppointmentData.service_name}</div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCancelModal() {
    const modal = document.getElementById('cancelAppointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function cancelAppointment() {
    if (!currentAppointmentId) {
        showNotification('Помилка: ID запису не знайдено', 'error');
        return;
    }

    fetch('/admin/appointments/' + currentAppointmentId + '/cancel', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (response.status === 422) {
            return response.json().then(data => {
                throw new Error(data.message || 'Неможливо скасувати цю запис');
            });
        }
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(data => {
        showNotification('Запис успішно скасовано', 'success');
        closeCancelModal();
        closeModal();

        // Перезагружуємо шкалу часу щоб видалити запис
        reloadTimeline(currentDayIndex);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Помилка при скасуванні запису', 'error');
    });
}

// ============================================
// Перенесення запису - функції
// ============================================

var currentRescheduleAppointmentData = null;
var selectedRescheduleSlot = null;

function openRescheduleModal() {
    if (!currentAppointmentId) {
        showNotification('Помилка: ID запису не знайдено', 'error');
        return;
    }

    const modal = document.getElementById('rescheduleAppointmentModal');
    const infoDiv = document.getElementById('rescheduleAppointmentInfo');

    // Показуємо інформацію про запис
    infoDiv.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas fa-user text-gray-400 text-sm"></i>
            <span class="font-medium">${currentAppointmentData.client_name}</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-user-md text-gray-400 text-sm"></i>
            <span class="text-sm text-gray-600">${currentAppointmentData.master_name}</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-concierge-bell text-gray-400 text-sm"></i>
            <span class="text-sm text-gray-600">${currentAppointmentData.service_name}</span>
        </div>
    `;

    // Скидаємо форму
    document.getElementById('rescheduleDate').value = '';
    document.getElementById('rescheduleSlotsContainer').innerHTML =
        '<div class="text-sm text-gray-500 text-center py-4">Оберіть дату для перегляду доступних слотів</div>';
    document.getElementById('confirmRescheduleBtn').disabled = true;
    selectedRescheduleSlot = null;
    resetRescheduleCustomTime();

    // Встановлюємо мінімальну дату (завтра)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('rescheduleDate').min = tomorrow.toISOString().split('T')[0];

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRescheduleModal() {
    const modal = document.getElementById('rescheduleAppointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    selectedRescheduleSlot = null;
}

function loadRescheduleSlots() {
    const date = document.getElementById('rescheduleDate').value;

    if (!date || !currentAppointmentData) {
        return;
    }

    const container = document.getElementById('rescheduleSlotsContainer');
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-xl text-gray-400"></i></div>';
    document.getElementById('confirmRescheduleBtn').disabled = true;
    selectedRescheduleSlot = null;

    // Завантажуємо доступні слоти
    fetch('/masters/' + currentAppointmentData.master_id + '/available-slots/' + date + '/' + currentAppointmentData.service_id)
        .then(response => response.json())
        .then(data => {
            const slots = Array.isArray(data) ? data : (data.slots || []);

            // Показуємо checkbox для кастомного часу
            document.getElementById('rescheduleCustomTimeCheckboxContainer').classList.remove('hidden');

            if (slots.length === 0) {
                container.innerHTML = '<p class="col-span-full text-center text-gray-500 py-4">На цю дату всі часи зайняті. Можна обрати кастомний час.</p>';
                return;
            }

            // Показуємо слоти
            container.innerHTML = '';
            const slotGrid = document.createElement('div');
            slotGrid.className = 'grid grid-cols-3 gap-2';
            slots.forEach(slot => {
                const slotBtn = document.createElement('button');
                slotBtn.type = 'button';
                slotBtn.className = 'px-3 py-2 text-sm border border-gray-300 rounded-lg transition-colors hover:border-blue-500 hover:bg-blue-50';
                slotBtn.textContent = slot;
                slotBtn.onclick = (e) => {
                    e.preventDefault();
                    selectRescheduleSlot(slot, slotBtn);
                };
                slotGrid.appendChild(slotBtn);
            });
            container.appendChild(slotGrid);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="col-span-full text-center text-red-500 py-4">Помилка завантаження слотів</p>';
        });
}

function selectRescheduleSlot(slot, element) {
    // Видаляємо селекцію з попереднього елемента
    document.querySelectorAll('#rescheduleSlotsContainer button').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
        btn.classList.add('border-gray-300');
    });

    // Додаємо селекцію до нового
    element.classList.remove('border-gray-300');
    element.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');

    selectedRescheduleSlot = slot;
    document.getElementById('confirmRescheduleBtn').disabled = false;

    // Скидаємо кастомний час
    resetRescheduleCustomTime();
}

function toggleRescheduleCustomTime() {
    const checkbox = document.getElementById('rescheduleCustomTimeCheckbox');
    const customInputs = document.getElementById('rescheduleCustomTimeInputs');

    if (checkbox.checked) {
        customInputs.classList.remove('hidden');
        selectedRescheduleSlot = null; // Очищуємо вибір слота
    } else {
        customInputs.classList.add('hidden');
        document.getElementById('rescheduleHour').value = '';
        document.getElementById('rescheduleMinute').value = '';
    }
}

function resetRescheduleCustomTime() {
    const checkbox = document.getElementById('rescheduleCustomTimeCheckbox');
    checkbox.checked = false;
    document.getElementById('rescheduleCustomTimeInputs').classList.add('hidden');
    document.getElementById('rescheduleHour').value = '';
    document.getElementById('rescheduleMinute').value = '';
}

function rescheduleAppointment() {
    const newDate = document.getElementById('rescheduleDate').value;
    let appointmentTime = selectedRescheduleSlot;

    // Проверяємо кастомний час
    const customCheckbox = document.getElementById('rescheduleCustomTimeCheckbox');
    if (customCheckbox.checked) {
        const hour = document.getElementById('rescheduleHour').value;
        const minute = document.getElementById('rescheduleMinute').value;

        if (!hour || !minute) {
            showNotification('Помилка: вкажіть час', 'error');
            return;
        }

        const hourNum = parseInt(hour);
        const minuteNum = parseInt(minute);

        if (hourNum < 0 || hourNum > 23 || minuteNum < 0 || minuteNum > 59) {
            showNotification('Помилка: невірний час', 'error');
            return;
        }

        appointmentTime = String(hourNum).padStart(2, '0') + ':' + String(minuteNum).padStart(2, '0');
    } else if (!appointmentTime) {
        showNotification('Помилка: оберіть час або включіть кастомний час', 'error');
        return;
    }

    if (!currentAppointmentId || !newDate) {
        showNotification('Помилка: дата не вибрана', 'error');
        return;
    }

    fetch('/admin/appointments/' + currentAppointmentId + '/reschedule', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            appointment_date: newDate,
            appointment_time: appointmentTime
        })
    })
    .then(response => {
        if (response.status === 422) {
            return response.json().then(data => {
                throw new Error(data.message || 'Помилка валідації');
            });
        }
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(data => {
        showNotification('Запис успішно перенесено', 'success');
        closeRescheduleModal();
        closeModal();

        // Перезагружуємо шкалу часу щоб оновити календар
        reloadTimeline(currentDayIndex);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Помилка при перенесенні запису', 'error');
    });
}

// ============================================
// Швидкий запис - функції
// ============================================

var qaSearchTimeout;
var qaSelectedClientId = null;
var qaCurrentMasterWorkingHours = null;
var qaOutsideWorkingHoursConfirmed = false;
var qaTimeSetManually = false; // Чи було час встановлено вручну (кліком на ячейку)

/**
 * Відкрити модалку швидкого запису
 * @param {Object} options - Опціональні параметри
 * @param {number} options.masterId - ID мастера для предзаповнення
 * @param {string} options.time - Час у форматі "HH:MM"
 */
function openQuickAppointmentModal(options) {
    options = options || {};

    var modal = document.getElementById('quickAppointmentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Скидаємо форму
    resetQuickAppointmentForm();

    // Встановлюємо дату з календаря
    var selectedDate = calendarData.weekDates[currentDayIndex];
    document.getElementById('qa_date').value = selectedDate;

    // Ініціалізуємо обробники
    initQuickAppointmentHandlers();

    // Якщо передано masterId - предзаповнюємо
    if (options.masterId) {
        var masterSelect = document.getElementById('qa_master_id');
        masterSelect.value = options.masterId;

        // Позначаємо що час встановлено вручну (з кліку на ячейку)
        if (options.time) {
            qaTimeSetManually = true;
        }

        // Завантажуємо послуги мастера
        loadMasterServicesForQuickAppointment(options.masterId, function() {
            // Після завантаження послуг - встановлюємо час
            if (options.time) {
                var timeParts = options.time.split(':');
                document.getElementById('qa_hour').value = timeParts[0];
                document.getElementById('qa_minute').value = timeParts[1];
            }
        });
    } else {
        // Встановлюємо час: поточна година, 00 хвилин
        var now = new Date();
        document.getElementById('qa_hour').value = String(now.getHours()).padStart(2, '0');
        document.getElementById('qa_minute').value = '00';
    }
}

/**
 * Завантажити послуги мастера для швидкого запису
 */
function loadMasterServicesForQuickAppointment(masterId, callback) {
    var serviceSelect = document.getElementById('qa_service_id');

    serviceSelect.disabled = true;
    serviceSelect.innerHTML = '<option value="">Завантаження...</option>';

    fetch('/admin/appointments/master-services?master_id=' + masterId)
        .then(function(r) { return r.json(); })
        .then(function(services) {
            serviceSelect.disabled = false;
            serviceSelect.innerHTML = '<option value="">Оберіть послугу</option>';
            services.forEach(function(service) {
                var option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name + ' (' + service.duration + ' хв)';
                serviceSelect.appendChild(option);
            });

            if (callback) callback();
        })
        .catch(function() {
            serviceSelect.disabled = false;
            serviceSelect.innerHTML = '<option value="">Помилка завантаження</option>';
            if (callback) callback();
        });
}

/**
 * Обробник кліку на ячейку часу в календарі
 */
function handleTimeSlotClick(e) {
    var cell = e.target.closest('.time-slot-cell');
    if (!cell) return;

    // Ігноруємо нерабочі слоти
    if (cell.classList.contains('non-working')) return;

    // Ігноруємо якщо клікнули на картку запису
    if (e.target.closest('.appointment-card')) return;

    var masterColumn = cell.closest('.master-column');
    if (!masterColumn) return;

    var masterId = parseInt(masterColumn.dataset.masterId);
    var timeSlot = cell.dataset.timeSlot;

    if (!masterId || !timeSlot) return;

    // Візуальний ефект кліку
    cell.classList.add('clicked');
    setTimeout(function() {
        cell.classList.remove('clicked');
    }, 300);

    // Відкриваємо модалку з параметрами
    openQuickAppointmentModal({
        masterId: masterId,
        time: timeSlot
    });
}

/**
 * Ініціалізація обробників кліку на ячейки часу
 */
function initTimeSlotClickHandlers() {
    var calendarBody = document.querySelector('.calendar-body');
    if (!calendarBody) return;

    // Видаляємо попередній обробник якщо є
    calendarBody.removeEventListener('click', handleTimeSlotClick);

    // Додаємо делегований обробник на весь контейнер (ефективніше для мобільних)
    calendarBody.addEventListener('click', handleTimeSlotClick);

    // Для мобільних: швидкий відгук без затримки 300ms
    calendarBody.style.touchAction = 'manipulation';
}

function closeQuickAppointmentModal() {
    var modal = document.getElementById('quickAppointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    resetQuickAppointmentForm();
}

function resetQuickAppointmentForm() {
    document.getElementById('qa_master_id').value = '';
    document.getElementById('qa_service_id').innerHTML = '<option value="">Спочатку оберіть майстра</option>';
    document.getElementById('qa_service_id').disabled = true;
    document.getElementById('qa_hour').value = '';
    document.getElementById('qa_minute').value = '';
    document.getElementById('qa_client_search').value = '';
    document.getElementById('qa_existing_client').value = '';
    document.getElementById('qa_new_client_name').value = '';
    document.getElementById('qa_new_client_phone').value = '';
    document.getElementById('qa_errors').classList.add('hidden');
    document.getElementById('qa_client_results').innerHTML = '<div class="p-4 text-center text-gray-500 text-sm"><i class="fas fa-search text-gray-400 mr-1"></i>Введіть мін. 2 символи</div>';

    // Скидаємо на "Існуючий клієнт"
    document.querySelector('input[name="qa_client_type"][value="existing"]').checked = true;
    document.getElementById('qa_existing_client_block').classList.remove('hidden');
    document.getElementById('qa_new_client_block').classList.add('hidden');

    // Скидаємо стан кнопки
    var btn = document.getElementById('qa_submit_btn');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i><span>Створити запис</span>';

    qaSelectedClientId = null;
    qaCurrentMasterWorkingHours = null;
    qaOutsideWorkingHoursConfirmed = false;
    qaTimeSetManually = false;

    // Видаляємо попередження якщо є
    var warning = document.getElementById('qa_working_hours_warning');
    if (warning) warning.remove();
}

// Функція для завантаження першого вільного слота (швидкий запис)
function qaLoadFirstAvailableSlot() {
    var masterId = document.getElementById('qa_master_id').value;
    var serviceId = document.getElementById('qa_service_id').value;
    var date = document.getElementById('qa_date').value;

    if (!masterId || !serviceId || !date) {
        return;
    }

    fetch('/masters/' + masterId + '/first-slot/' + date + '/' + serviceId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                // Зберігаємо робочий час для валідації
                qaCurrentMasterWorkingHours = data.working_hours;
                qaOutsideWorkingHoursConfirmed = false;

                // Видаляємо попередні попередження
                var oldWarning = document.getElementById('qa_working_hours_warning');
                if (oldWarning) oldWarning.remove();

                // Якщо час встановлено вручну (кліком на ячейку) - не змінюємо його
                if (qaTimeSetManually) {
                    // Але показуємо попередження якщо день нерабочий
                    if (!data.is_working_day) {
                        qaShowWorkingHoursWarning('Майстер не працює в цей день');
                    }
                    return;
                }

                if (data.is_working_day && data.first_available_slot) {
                    // Встановлюємо перший вільний слот
                    var parts = data.first_available_slot.split(':');
                    document.getElementById('qa_hour').value = parts[0];
                    document.getElementById('qa_minute').value = parts[1];
                } else if (!data.is_working_day) {
                    // Майстер не працює - показуємо попередження
                    qaShowWorkingHoursWarning('Майстер не працює в цей день');
                    document.getElementById('qa_hour').value = '09';
                    document.getElementById('qa_minute').value = '00';
                } else {
                    // Немає вільних слотів
                    qaShowWorkingHoursWarning('Немає вільних слотів у робочий час');
                }
            }
        })
        .catch(function(error) {
            console.error('Error loading first slot:', error);
        });
}

// Показати попередження в швидкому записі
function qaShowWorkingHoursWarning(message) {
    var oldWarning = document.getElementById('qa_working_hours_warning');
    if (oldWarning) oldWarning.remove();

    var warningDiv = document.createElement('div');
    warningDiv.id = 'qa_working_hours_warning';
    warningDiv.className = 'bg-yellow-50 border border-yellow-200 text-yellow-700 px-3 py-2 rounded text-sm mb-2';
    warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>' + message;

    var dateRow = document.getElementById('qa_date').closest('.grid');
    dateRow.parentNode.insertBefore(warningDiv, dateRow.nextSibling);
}

function initQuickAppointmentHandlers() {
    // Обробник зміни майстра
    var masterSelect = document.getElementById('qa_master_id');
    masterSelect.onchange = function() {
        var masterId = this.value;
        var serviceSelect = document.getElementById('qa_service_id');

        // Скидаємо робочий час
        qaCurrentMasterWorkingHours = null;
        qaOutsideWorkingHoursConfirmed = false;

        if (!masterId) {
            serviceSelect.innerHTML = '<option value="">Спочатку оберіть майстра</option>';
            serviceSelect.disabled = true;
            return;
        }

        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">Завантаження...</option>';

        fetch('/admin/appointments/master-services?master_id=' + masterId)
            .then(function(r) { return r.json(); })
            .then(function(services) {
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="">Оберіть послугу</option>';
                services.forEach(function(service) {
                    var option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name + ' (' + service.duration + ' хв)';
                    serviceSelect.appendChild(option);
                });
            })
            .catch(function() {
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="">Помилка завантаження</option>';
            });
    };

    // Обробник зміни послуги - завантажуємо перший вільний слот
    var serviceSelect = document.getElementById('qa_service_id');
    serviceSelect.onchange = function() {
        if (this.value) {
            qaLoadFirstAvailableSlot();
        }
    };

    // Обробник зміни дати - завантажуємо перший вільний слот
    var dateInput = document.getElementById('qa_date');
    dateInput.onchange = function() {
        qaOutsideWorkingHoursConfirmed = false;
        qaLoadFirstAvailableSlot();
    };

    // Обробник типу клієнта
    document.querySelectorAll('input[name="qa_client_type"]').forEach(function(radio) {
        radio.onchange = function() {
            if (this.value === 'existing') {
                document.getElementById('qa_existing_client_block').classList.remove('hidden');
                document.getElementById('qa_new_client_block').classList.add('hidden');
            } else {
                document.getElementById('qa_existing_client_block').classList.add('hidden');
                document.getElementById('qa_new_client_block').classList.remove('hidden');
            }
        };
    });

    // Пошук клієнтів
    var searchInput = document.getElementById('qa_client_search');
    searchInput.oninput = function() {
        clearTimeout(qaSearchTimeout);
        var query = this.value.trim();
        var resultsContainer = document.getElementById('qa_client_results');

        if (query.length < 2) {
            resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm"><i class="fas fa-search text-gray-400 mr-1"></i>Введіть мін. 2 символи</div>';
            return;
        }

        resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin"></i></div>';

        qaSearchTimeout = setTimeout(function() {
            fetch('/admin/appointments/search-clients?q=' + encodeURIComponent(query))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.results.length === 0) {
                        resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Не знайдено</div>';
                        return;
                    }

                    resultsContainer.innerHTML = data.results.map(function(client) {
                        var isSelected = qaSelectedClientId == client.id;
                        var emailHtml = client.email ? '<span class="ml-2"><i class="fas fa-envelope text-gray-400 mr-1"></i>' + client.email + '</span>' : '';
                        var telegramHtml = client.telegram_username ? '<a href="https://t.me/' + client.telegram_username + '" target="_blank" onclick="event.stopPropagation()" class="ml-2 text-blue-600 hover:text-blue-800"><i class="fab fa-telegram mr-1"></i>@' + client.telegram_username + '</a>' : '';
                        var descriptionHtml = client.description ? '<div class="text-xs text-gray-500 mt-1 bg-gray-100 p-1.5 rounded"><i class="fas fa-info-circle mr-1"></i>' + client.description + '</div>' : '';

                        return '<label class="flex items-start p-3 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0 ' + (isSelected ? 'bg-green-50' : '') + '">' +
                            '<input type="radio" name="qa_client_radio" value="' + client.id + '" ' + (isSelected ? 'checked' : '') + ' class="mr-3 mt-1">' +
                            '<div class="flex-1 min-w-0">' +
                                '<div class="font-medium text-sm">' + client.name + '</div>' +
                                '<div class="text-xs text-gray-600 mt-0.5 flex flex-wrap items-center">' +
                                    '<span><i class="fas fa-phone text-gray-400 mr-1"></i>' + client.phone + '</span>' +
                                    emailHtml +
                                    telegramHtml +
                                '</div>' +
                                descriptionHtml +
                            '</div>' +
                        '</label>';
                    }).join('');

                    // Обробка вибору
                    document.querySelectorAll('input[name="qa_client_radio"]').forEach(function(radio) {
                        radio.onchange = function() {
                            qaSelectedClientId = this.value;
                            document.getElementById('qa_existing_client').value = this.value;

                            // Візуальна індикація
                            document.querySelectorAll('input[name="qa_client_radio"]').forEach(function(r) {
                                r.closest('label').classList.remove('bg-green-50');
                            });
                            this.closest('label').classList.add('bg-green-50');
                        };
                    });
                })
                .catch(function() {
                    resultsContainer.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Помилка</div>';
                });
        }, 300);
    };

    // Форматування телефону
    var phoneInput = document.getElementById('qa_new_client_phone');
    phoneInput.oninput = function() {
        var value = this.value.replace(/\D/g, '');
        if (value.startsWith('380')) value = value.substring(3);
        if (value.length > 0) {
            value = '+380 ' + value.replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
        }
        this.value = value.trim();
    };

    // Форматування часу
    document.getElementById('qa_hour').onblur = formatQaTime;
    document.getElementById('qa_minute').onblur = formatQaTime;
}

function formatQaTime() {
    var hourInput = document.getElementById('qa_hour');
    var minuteInput = document.getElementById('qa_minute');

    var hour = parseInt(hourInput.value);
    var minute = parseInt(minuteInput.value);

    if (!isNaN(hour)) {
        hour = Math.max(0, Math.min(23, hour));
        hourInput.value = String(hour).padStart(2, '0');
    }

    if (!isNaN(minute)) {
        minute = Math.max(0, Math.min(59, minute));
        minuteInput.value = String(minute).padStart(2, '0');
    }
}

// ============================================
// Кастомний confirm-діалог в стилі адмінки
// ============================================
function showConfirmDialog(options) {
    var title = options.title || 'Підтвердження';
    var message = options.message || '';
    var confirmText = options.confirmText || 'Так';
    var cancelText = options.cancelText || 'Скасувати';
    var type = options.type || 'warning'; // warning, danger, info
    var onConfirm = options.onConfirm || function() {};
    var onCancel = options.onCancel || function() {};

    // Кольори в залежності від типу
    var iconColor, iconBg, buttonColor;
    if (type === 'danger') {
        iconColor = 'text-red-600';
        iconBg = 'bg-red-100';
        buttonColor = 'bg-red-600 hover:bg-red-700';
    } else if (type === 'info') {
        iconColor = 'text-blue-600';
        iconBg = 'bg-blue-100';
        buttonColor = 'bg-blue-600 hover:bg-blue-700';
    } else {
        iconColor = 'text-yellow-600';
        iconBg = 'bg-yellow-100';
        buttonColor = 'bg-yellow-600 hover:bg-yellow-700';
    }

    // Створюємо HTML модалки
    var modalHtml = '<div id="customConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] p-4">' +
        '<div class="bg-white rounded-lg max-w-md w-full shadow-xl transform transition-all">' +
            '<div class="p-6">' +
                '<div class="flex items-start gap-4">' +
                    '<div class="flex-shrink-0 w-12 h-12 rounded-full ' + iconBg + ' flex items-center justify-center">' +
                        '<i class="fas fa-exclamation-triangle text-xl ' + iconColor + '"></i>' +
                    '</div>' +
                    '<div class="flex-1">' +
                        '<h3 class="text-lg font-semibold text-gray-900 mb-2">' + title + '</h3>' +
                        '<p class="text-gray-600 text-sm">' + message + '</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">' +
                '<button id="confirmDialogCancel" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">' +
                    cancelText +
                '</button>' +
                '<button id="confirmDialogConfirm" class="px-4 py-2 text-white rounded-lg transition-colors ' + buttonColor + '">' +
                    confirmText +
                '</button>' +
            '</div>' +
        '</div>' +
    '</div>';

    // Видаляємо попередній діалог якщо є
    var existingModal = document.getElementById('customConfirmModal');
    if (existingModal) existingModal.remove();

    // Додаємо модалку
    var wrapper = document.createElement('div');
    wrapper.innerHTML = modalHtml;
    var modal = wrapper.firstChild;
    document.body.appendChild(modal);

    // Анімація появи
    modal.style.opacity = '0';
    setTimeout(function() {
        modal.style.transition = 'opacity 0.2s';
        modal.style.opacity = '1';
    }, 10);

    // Функція закриття
    function closeModal() {
        modal.style.opacity = '0';
        setTimeout(function() {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 200);
    }

    // Обробники
    modal.querySelector('#confirmDialogCancel').onclick = function() {
        closeModal();
        onCancel();
    };

    modal.querySelector('#confirmDialogConfirm').onclick = function() {
        closeModal();
        onConfirm();
    };

    // Закриття по кліку на overlay
    modal.onclick = function(e) {
        if (e.target === modal) {
            closeModal();
            onCancel();
        }
    };

    // Закриття по Escape
    var escHandler = function(e) {
        if (e.key === 'Escape') {
            closeModal();
            onCancel();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

// Перевірка чи час у межах робочого часу майстра
function qaIsTimeInWorkingHours(hour, minute) {
    if (!qaCurrentMasterWorkingHours) {
        return true; // Якщо немає даних - не блокуємо
    }

    var timeStr = String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
    var start = qaCurrentMasterWorkingHours.start;
    var end = qaCurrentMasterWorkingHours.end;

    return timeStr >= start && timeStr < end;
}

function submitQuickAppointment() {
    var errorsDiv = document.getElementById('qa_errors');
    errorsDiv.classList.add('hidden');

    // Збираємо дані
    var masterId = document.getElementById('qa_master_id').value;
    var serviceId = document.getElementById('qa_service_id').value;
    var date = document.getElementById('qa_date').value;
    var hour = document.getElementById('qa_hour').value;
    var minute = document.getElementById('qa_minute').value;
    var clientType = document.querySelector('input[name="qa_client_type"]:checked').value;

    // Валідація
    var errors = [];
    if (!masterId) errors.push('Оберіть майстра');
    if (!serviceId) errors.push('Оберіть послугу');
    if (!date) errors.push('Оберіть дату');
    if (!hour || !minute) errors.push('Вкажіть час');

    if (clientType === 'existing') {
        if (!document.getElementById('qa_existing_client').value) {
            errors.push('Оберіть клієнта');
        }
    } else {
        if (!document.getElementById('qa_new_client_name').value) errors.push('Вкажіть ім\'я клієнта');
        if (!document.getElementById('qa_new_client_phone').value) errors.push('Вкажіть телефон клієнта');
    }

    if (errors.length > 0) {
        errorsDiv.innerHTML = errors.join('<br>');
        errorsDiv.classList.remove('hidden');
        return;
    }

    // Форматуємо час
    formatQaTime();
    var formattedHour = document.getElementById('qa_hour').value;
    var formattedMinute = document.getElementById('qa_minute').value;
    var time = formattedHour + ':' + formattedMinute;

    // Перевірка робочого часу (якщо ще не підтверджено)
    if (!qaOutsideWorkingHoursConfirmed && qaCurrentMasterWorkingHours) {
        var hourInt = parseInt(formattedHour);
        var minuteInt = parseInt(formattedMinute);

        if (!qaIsTimeInWorkingHours(hourInt, minuteInt)) {
            var workingHoursText = qaCurrentMasterWorkingHours.start + ' - ' + qaCurrentMasterWorkingHours.end;

            showConfirmDialog({
                title: 'Час поза робочим графіком',
                message: 'Обраний час <strong>' + time + '</strong> знаходиться поза робочим часом майстра <strong>(' + workingHoursText + ')</strong>.<br><br>Ви впевнені, що хочете створити запис на цей час?',
                confirmText: 'Так, створити',
                cancelText: 'Скасувати',
                type: 'warning',
                onConfirm: function() {
                    qaOutsideWorkingHoursConfirmed = true;
                    submitQuickAppointment(); // Повторно викликаємо з підтвердженням
                }
            });
            return;
        }
    }

    var btn = document.getElementById('qa_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Створення...</span>';

    var data = {
        master_id: masterId,
        service_id: serviceId,
        appointment_date: date,
        appointment_time: time,
        client_type: clientType
    };

    if (clientType === 'existing') {
        data.existing_client = document.getElementById('qa_existing_client').value;
    } else {
        data.new_client_name = document.getElementById('qa_new_client_name').value;
        data.new_client_phone = document.getElementById('qa_new_client_phone').value;
    }

    fetch('/admin/appointments/quick-store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        return response.json().then(function(d) {
            return { status: response.status, data: d };
        });
    })
    .then(function(result) {
        if (result.data.success) {
            showNotification(result.data.message, 'success');

            // Додаємо запис до календаря
            if (result.data.appointment) {
                addNewAppointmentToCalendar(result.data.appointment);
            }

            closeQuickAppointmentModal();
        } else {
            var errorMsg = result.data.message;
            if (result.data.errors) {
                var errorList = [];
                for (var field in result.data.errors) {
                    errorList = errorList.concat(result.data.errors[field]);
                }
                errorMsg = errorList.join('<br>');
            }
            errorsDiv.innerHTML = errorMsg;
            errorsDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i><span>Створити запис</span>';
        }
    })
    .catch(function(error) {
        errorsDiv.innerHTML = 'Помилка мережі: ' + error.message;
        errorsDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i><span>Створити запис</span>';
    });
}

</script>
@endpush
@endsection