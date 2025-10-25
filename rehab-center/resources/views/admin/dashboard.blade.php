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

<!-- Календар розкладу -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4 lg:p-6 border-b">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg lg:text-xl font-semibold">Розклад записів</h3>
                <p class="text-xs lg:text-sm text-gray-500 mt-1">
                    {{ $calendar['startDate']->format('d.m.Y') }} - {{ $calendar['endDate']->format('d.m.Y') }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="?week=previous" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm transition">
                    <i class="fas fa-chevron-left"></i>
                    <span class="hidden lg:inline ml-1">Попередній</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded text-sm transition">
                    <i class="fas fa-calendar"></i>
                    <span class="hidden lg:inline ml-1">Сьогодні</span>
                </a>
                <a href="?week=next" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm transition">
                    <span class="hidden lg:inline mr-1">Наступний</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Десктоп версія -->
    <div class="hidden lg:block overflow-x-auto p-4">
        @foreach($calendar['masters'] as $master)
            <div class="master-section mb-6 last:mb-0">
                <div class="master-header bg-gradient-to-r from-blue-50 to-purple-50 border border-gray-200 rounded-t-lg px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if($master->photo)
                            <img src="{{ asset('storage/' . $master->photo) }}" alt="{{ $master->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-white">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm border-2 border-white">
                                {{ substr($master->name, 0, 2) }}
                            </div>
                        @endif
                        <div>
                            <div class="font-semibold text-gray-800">{{ $master->name }}</div>
                            @if($master->specialty)
                                <div class="text-xs text-gray-500">{{ $master->specialty }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border border-t-0 border-gray-200 rounded-b-lg overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border-r border-gray-200 px-3 py-2 text-center text-xs font-semibold text-gray-600" style="width: 70px;">Час</th>
                                @foreach($calendar['weekDates'] as $date)
                                    <th class="border-r last:border-r-0 border-gray-200 px-2 py-2 text-center text-xs font-semibold {{ $date->isToday() ? 'bg-blue-50' : '' }}">
                                        <div class="{{ $date->isToday() ? 'text-blue-600' : 'text-gray-700' }}">{{ $date->isoFormat('dd') }}</div>
                                        <div class="text-xs {{ $date->isToday() ? 'text-blue-500' : 'text-gray-500' }} font-normal">{{ $date->format('d.m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($calendar['timeSlots'] as $timeSlot)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="border-r border-t border-gray-200 px-2 py-3 text-xs font-medium text-gray-600 text-center bg-gray-50">{{ $timeSlot }}</td>
                                    @foreach($calendar['weekDates'] as $date)
                                        <td class="border-r border-t last:border-r-0 border-gray-200 p-1 align-top {{ $date->isToday() ? 'bg-blue-50/30' : '' }}">
                                            @php
                                                $dateKey = $date->format('Y-m-d');
                                                $dayAppointments = $calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? [];
                                                
                                                // Фільтруємо записи для цього часового слоту
                                                $slotAppointments = collect($dayAppointments)->filter(function($apt) use ($timeSlot) {
                                                    return substr($apt['time'], 0, 5) === $timeSlot;
                                                });
                                            @endphp

                                            @foreach($slotAppointments as $appointment)
                                                <div class="appointment-card cursor-pointer" onclick="showAppointmentDetails({{ $appointment['id'] }})">
                                                    <div class="text-xs font-bold mb-1">{{ substr($appointment['time'], 0, 5) }}</div>
                                                    <div class="text-xs font-semibold mb-0.5">{{ $appointment['client_name'] }}</div>
                                                    <div class="text-xs opacity-90 mb-1">{{ Str::limit($appointment['service_name'], 20) }}</div>
                                                    <div class="text-xs font-bold">{{ number_format($appointment['price'], 0) }} грн</div>
                                                </div>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @if($calendar['masters']->count() === 0)
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-user-slash text-4xl mb-3"></i>
                <p>Немає активних майстрів</p>
            </div>
        @endif
    </div>

    <!-- Мобільна версія -->
    <div class="lg:hidden">
        <div class="flex overflow-x-auto gap-2 p-4 border-b">
            @foreach($calendar['weekDates'] as $index => $date)
                <button onclick="selectMobileDate({{ $index }})" class="mobile-date-btn flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $date->isToday() ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700' }}" data-date-index="{{ $index }}">
                    <div>{{ $date->isoFormat('dd') }}</div>
                    <div class="text-xs">{{ $date->format('d.m') }}</div>
                </button>
            @endforeach
        </div>

        <div id="mobile-schedule-container">
            @foreach($calendar['weekDates'] as $dayIndex => $date)
                <div class="mobile-date-schedule {{ $dayIndex === 0 ? '' : 'hidden' }}" data-date-index="{{ $dayIndex }}">
                    @foreach($calendar['masters'] as $master)
                        @php
                            $dateKey = $date->format('Y-m-d');
                            $dayAppointments = collect($calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? [])
                                ->sortBy(function($apt) {
                                    return $apt['time'];
                                });
                        @endphp
                        @if($dayAppointments->count() > 0)
                            <div class="border-b last:border-b-0">
                                <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($master->photo)
                                            <img src="{{ asset('storage/' . $master->photo) }}" alt="{{ $master->name }}" class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                                {{ substr($master->name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-gray-800">{{ $master->name }}</div>
                                            @if($master->specialty)
                                                <div class="text-xs text-gray-500">{{ $master->specialty }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 space-y-2">
                                    @foreach($dayAppointments as $appointment)
                                        <div class="appointment-card-mobile cursor-pointer" onclick="showAppointmentDetails({{ $appointment['id'] }})">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="text-sm font-bold text-white">{{ substr($appointment['time'], 0, 5) }}</div>
                                                <div class="text-sm font-bold">{{ number_format($appointment['price'], 0) }} грн</div>
                                            </div>
                                            <div class="text-sm font-semibold mb-1">{{ $appointment['client_name'] }}</div>
                                            <div class="text-xs opacity-90 mb-1">{{ $appointment['service_name'] }}</div>
                                            <div class="text-xs opacity-75"><i class="far fa-clock"></i> {{ $appointment['duration'] }} хв</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @php
                        $hasAppointments = false;
                        foreach($calendar['masters'] as $master) {
                            $dateKey = $date->format('Y-m-d');
                            $check = $calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? [];
                            if(count($check) > 0) {
                                $hasAppointments = true;
                                break;
                            }
                        }
                    @endphp
                    @if(!$hasAppointments)
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-3"></i>
                            <p class="text-sm">Немає записів на цей день</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Модальне вікно -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 lg:p-6 border-b sticky top-0 bg-white z-10">
            <h3 class="text-lg font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="appointmentContent" class="p-4 lg:p-6"></div>
        <div class="flex justify-end gap-2 p-4 lg:p-6 border-t bg-gray-50 sticky bottom-0">
            <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition text-sm lg:text-base">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
.appointment-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    min-height: 80px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.appointment-card-mobile {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.appointment-card-mobile:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.mobile-date-btn.active {
    background-color: #3b82f6;
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
function selectMobileDate(index) {
    document.querySelectorAll('.mobile-date-btn').forEach(function(btn, i) {
        if (i === index) {
            btn.classList.add('active', 'bg-blue-500', 'text-white');
            btn.classList.remove('bg-gray-100', 'text-gray-700');
        } else {
            btn.classList.remove('active', 'bg-blue-500', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        }
    });

    document.querySelectorAll('.mobile-date-schedule').forEach(function(schedule, i) {
        if (i === index) {
            schedule.classList.remove('hidden');
        } else {
            schedule.classList.add('hidden');
        }
    });
}

function showAppointmentDetails(appointmentId) {
    var modal = document.getElementById('appointmentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="text-gray-500 mt-2 text-sm">Завантаження...</p></div>';
    
    fetch('/admin/appointments/' + appointmentId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var statusClasses = {
                'scheduled': 'bg-green-100 text-green-800',
                'completed': 'bg-blue-100 text-blue-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            var statusClass = statusClasses[data.status] || 'bg-gray-100 text-gray-800';
            
            var html = '<div class="space-y-4">';
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Клієнт</h4>';
            html += '<p class="text-base font-medium">' + data.client.name + '</p>';
            html += '<p class="text-sm text-gray-600">' + data.client.phone + '</p>';
            if (data.client.email) {
                html += '<p class="text-sm text-gray-600">' + data.client.email + '</p>';
            }
            html += '</div>';
            
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Майстер</h4>';
            html += '<p class="text-base font-medium">' + data.master.name + '</p>';
            if (data.master.phone) {
                html += '<p class="text-sm text-gray-600">' + data.master.phone + '</p>';
            }
            html += '</div>';
            
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Послуга</h4>';
            html += '<p class="text-base font-medium">' + data.service.name + '</p>';
            html += '<p class="text-sm text-gray-600">Тривалість: ' + data.service.duration + ' хв</p>';
            html += '</div>';
            
            html += '<div class="grid grid-cols-2 gap-4">';
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Дата</h4>';
            html += '<p class="font-medium">' + data.appointment_date + '</p></div>';
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Час</h4>';
            html += '<p class="font-medium">' + data.appointment_time + '</p></div>';
            html += '</div>';
            
            html += '<div class="grid grid-cols-2 gap-4">';
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Ціна</h4>';
            html += '<p class="text-lg font-bold text-green-600">' + data.price + ' грн</p></div>';
            html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Статус</h4>';
            html += '<span class="px-2 py-1 text-xs font-semibold rounded-full ' + statusClass + '">' + data.status_text + '</span></div>';
            html += '</div>';
            
            if (data.notes) {
                html += '<div><h4 class="font-semibold text-gray-700 mb-2 text-sm">Примітки</h4>';
                html += '<p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">' + data.notes + '</p></div>';
            }
            
            html += '</div>';
            
            document.getElementById('appointmentContent').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('appointmentContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-400"></i><p class="text-red-500 mt-2 text-sm">Помилка завантаження</p></div>';
        });
}

function closeModal() {
    var modal = document.getElementById('appointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 1024) {
        var todayBtn = document.querySelector('.mobile-date-btn.bg-blue-500');
        if (todayBtn) {
            var index = parseInt(todayBtn.dataset.dateIndex);
            selectMobileDate(index);
        }
    }
});
</script>
@endpush
@endsection