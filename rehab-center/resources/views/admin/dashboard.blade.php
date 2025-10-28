@extends('layouts.admin')

@section('title', 'Панель управління')
@section('page-title', 'Розклад')

@section('content')

<!-- Верхня панель: дата + навігація -->
<div class="bg-white rounded-lg shadow-sm mb-3 p-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="?week=previous" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition">
            <i class="fas fa-chevron-left text-gray-600"></i>
        </a>
        
        <div class="flex items-center gap-2">
            <i class="fas fa-calendar text-blue-600"></i>
            <span class="font-semibold text-lg">{{ now()->addWeeks(session('week_offset', 0))->format('d.m') }}</span>
        </div>
        
        <a href="?week=next" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition">
            <i class="fas fa-chevron-right text-gray-600"></i>
        </a>
    </div>
    
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
        Сьогодні
    </a>
</div>

<!-- Горизонтальні табі днів -->
<div class="bg-white rounded-lg shadow-sm mb-3 overflow-hidden">
    <div class="flex overflow-x-auto hide-scrollbar">
        @foreach($calendar['weekDates'] as $index => $date)
            <button onclick="selectDay({{ $index }})" 
                    data-day-index="{{ $index }}"
                    class="day-tab flex-shrink-0 flex flex-col items-center justify-center px-6 py-3 border-r last:border-r-0 transition-colors {{ $date->isToday() ? 'bg-blue-500 text-white' : 'hover:bg-gray-50' }}">
                <div class="text-xs font-medium {{ $date->isToday() ? 'text-blue-100' : 'text-gray-500' }}">
                    {{ $date->isoFormat('dd') }}
                </div>
                <div class="text-xl font-bold mt-1">
                    {{ $date->format('d') }}
                </div>
            </button>
        @endforeach
    </div>
</div>

<!-- Контент по днях -->
@foreach($calendar['weekDates'] as $dayIndex => $date)
    <div class="day-content {{ $dayIndex === 0 ? '' : 'hidden' }}" data-day-index="{{ $dayIndex }}">
        
        @php
            $dateKey = $date->format('Y-m-d');
            $hasMasters = false;
        @endphp

        <!-- Горизонтальний скрол майстрів -->
        <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-3">
            @foreach($calendar['masters'] as $master)
                @php
                    $dayAppointments = collect($calendar['scheduleByMaster'][$master->id]['appointments_by_date'][$dateKey] ?? []);
                    if ($dayAppointments->count() > 0) $hasMasters = true;
                @endphp
                
                <div class="flex-shrink-0 w-80 bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Заголовок майстра -->
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-4 py-3 border-b">
                        <div class="flex items-center gap-3">
                            @if($master->photo)
                                <img src="{{ asset('storage/' . $master->photo) }}" 
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                    {{ substr($master->name, 0, 2) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-900 truncate">{{ $master->name }}</div>
                                @if($master->specialty)
                                    <div class="text-xs text-gray-500 truncate">{{ $master->specialty }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Часова сітка -->
                    <div class="p-2 space-y-1 max-h-[calc(100vh-300px)] overflow-y-auto">
                        @php
                            // Визначаємо всі слоти для цього майстра
                            $masterSlots = [];
                            foreach($dayAppointments as $apt) {
                                $time = substr($apt['time'], 0, 5);
                                if (!isset($masterSlots[$time])) {
                                    $masterSlots[$time] = [];
                                }
                                $masterSlots[$time][] = $apt;
                            }
                            ksort($masterSlots);
                        @endphp

                        @if(count($masterSlots) > 0)
                            @foreach($masterSlots as $timeSlot => $appointments)
                                <div class="border-l-4 border-blue-500 bg-gray-50 rounded">
                                    <!-- Час -->
                                    <div class="px-3 py-2 bg-white border-b">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-gray-700">{{ $timeSlot }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Записи в цей час -->
                                    @foreach($appointments as $apt)
                                        <div class="px-3 py-3 cursor-pointer hover:bg-blue-50 transition-colors"
                                             onclick="showAppointmentDetails({{ $apt['id'] }})">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="font-semibold text-gray-900">
                                                    {{ $apt['client_name'] }}
                                                </div>
                                                <div class="text-sm font-bold text-green-600 ml-2">
                                                    {{ number_format($apt['price'], 0) }}₴
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600 mb-1">
                                                {{ $apt['service_name'] }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <i class="far fa-clock mr-1"></i>{{ $apt['duration'] }} хв
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-12 text-gray-400">
                                <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                <p class="text-sm">Немає записів</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if(!$hasMasters)
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg font-medium">Немає записів на цей день</p>
            </div>
        @endif

    </div>
@endforeach

<!-- Статистика -->
<div class="grid grid-cols-4 gap-2 mt-3">
    <div class="bg-white rounded-lg shadow-sm p-3 text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $stats['today'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Сьогодні</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center">
        <div class="text-2xl font-bold text-green-600">{{ $stats['week'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Тиждень</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center">
        <div class="text-2xl font-bold text-purple-600">{{ $stats['month'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Місяць</div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center">
        <div class="text-2xl font-bold text-orange-600">{{ $stats['upcoming'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Майбутні</div>
    </div>
</div>

<!-- Модалка -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="appointmentContent" class="p-4"></div>
        <div class="p-4 border-t bg-gray-50 sticky bottom-0">
            <button onclick="closeModal()" class="w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
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
.day-tab.active {
    background-color: #3b82f6 !important;
    color: white !important;
}
.day-tab.active .text-gray-500 {
    color: rgba(255,255,255,0.8) !important;
}
</style>
@endpush

@push('scripts')
<script>
function selectDay(index) {
    document.querySelectorAll('.day-tab').forEach((tab, i) => {
        if (i === index) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });
    
    document.querySelectorAll('.day-content').forEach((content, i) => {
        content.classList.toggle('hidden', i !== index);
    });
}

function showAppointmentDetails(id) {
    const modal = document.getElementById('appointmentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    document.getElementById('appointmentContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
    `;
    
    fetch('/admin/appointments/' + id)
        .then(r => r.json())
        .then(d => {
            const sc = {'scheduled':'bg-green-100 text-green-800','completed':'bg-blue-100 text-blue-800','cancelled':'bg-red-100 text-red-800'}[d.status];
            document.getElementById('appointmentContent').innerHTML = `
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Клієнт</div>
                        <div class="text-lg font-semibold">${d.client.name}</div>
                        <div class="text-sm text-gray-600">${d.client.phone}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Майстер</div>
                        <div class="font-medium">${d.master.name}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Послуга</div>
                        <div class="font-medium">${d.service.name}</div>
                        <div class="text-sm text-gray-600">${d.service.duration} хв</div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Дата</div>
                            <div class="font-medium">${d.appointment_date}</div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Час</div>
                            <div class="font-medium">${d.appointment_time}</div>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Ціна</div>
                            <div class="text-xl font-bold text-green-600">${d.price}₴</div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Статус</div>
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full ${sc}">${d.status_text}</span>
                        </div>
                    </div>
                    ${d.notes ? `<div><div class="text-xs text-gray-500 mb-1">Примітки</div><div class="text-sm bg-gray-50 p-3 rounded">${d.notes}</div></div>` : ''}
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

document.addEventListener('DOMContentLoaded', () => {
    const today = document.querySelector('.day-tab.active');
    today?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
});
</script>
@endpush
@endsection