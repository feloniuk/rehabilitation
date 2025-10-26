@extends('layouts.app')

@section('title', $master->name . ' - Спеціаліст')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">Головна</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="{{ route('home') }}#masters" class="hover:text-emerald-600 transition-colors">Спеціалісти</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-gray-700">{{ $master->name }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Master Profile -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl p-8 sticky top-24">
                <!-- Photo -->
                <div class="text-center mb-8">
                    <div class="relative inline-block">
                        <div class="w-40 h-40 rounded-full overflow-hidden mx-auto mb-4 border-4 border-emerald-100">
                            @if($master->photo)
                                <img src="{{ asset('storage/' . $master->photo) }}"
                                     alt="{{ $master->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                    <i class="fas fa-user text-4xl text-white"></i>
                                </div>
                            @endif
                        </div>
                        <!-- Online Status -->
                        <div class="absolute bottom-4 right-4 w-6 h-6 bg-green-400 rounded-full border-4 border-white"></div>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $master->name }}</h1>
                    <p class="text-emerald-600 font-medium mb-4">{{ $master->specialty ?: 'Спеціаліст з реабілітації' }}</p>
                    
                    <!-- Rating Stars -->
                    <div class="flex justify-center mb-6">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                        @endfor
                        <span class="text-sm text-gray-600 ml-2">(4.9)</span>
                    </div>
                </div>

                @if($master->description)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            <i class="fas fa-user-md text-emerald-500 mr-2"></i>
                            Про спеціаліста
                        </h3>
                        <p class="text-gray-600 leading-relaxed">{{ $master->description }}</p>
                    </div>
                @endif

                <!-- Quick Contact -->
                <div class="space-y-3 mb-8">
                    <a href="#services" 
                       class="block w-full bg-emerald-600 text-white py-3 px-6 rounded-full font-semibold text-center hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Записатися на прийом
                    </a>
                    
                    @if($master->phone)
                        <a href="tel:{{ $master->phone }}" 
                           class="block w-full border-2 border-emerald-600 text-emerald-600 py-3 px-6 rounded-full font-semibold text-center hover:bg-emerald-600 hover:text-white transition-all duration-300">
                            <i class="fas fa-phone mr-2"></i>
                            Зателефонувати
                        </a>
                    @endif
                </div>

                <!-- Working Hours -->
                @if($master->work_schedule)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-clock text-emerald-500 mr-2"></i>
                            Режим роботи
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @php
                                $days = [
                                    'monday' => 'Понеділок',
                                    'tuesday' => 'Вівторок',
                                    'wednesday' => 'Середа',
                                    'thursday' => 'Четвер',
                                    'friday' => 'П\'ятниця',
                                    'saturday' => 'Субота',
                                    'sunday' => 'Неділя'
                                ];
                                $today = strtolower(now()->format('l'));
                            @endphp
                            <div class="space-y-2">
                                @foreach($days as $dayKey => $dayName)
                                    <div class="flex justify-between items-center py-1 {{ $dayKey === $today ? 'font-semibold text-emerald-600' : 'text-gray-600' }}">
                                        <span>{{ $dayName }}</span>
                                        @if($master->isWorkingOnDay($dayKey))
                                            @php $hours = $master->getWorkingHours($dayKey); @endphp
                                            <span class="text-sm">{{ $hours['start'] }} - {{ $hours['end'] }}</span>
                                        @else
                                            <span class="text-sm text-gray-400">Вихідний</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Services & Booking -->
        <div class="lg:col-span-2">
            <!-- Services Section -->
            <section id="services" class="mb-12">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-bold text-gray-800">Послуги та ціни</h2>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ $master->masterServices->count() }} послуг
                        </div>
                    </div>

                    @if($master->masterServices->count() > 0)
                        <div class="space-y-6">
                            @foreach($master->masterServices as $masterService)
                                <div class="group border border-gray-200 rounded-xl p-6 hover:border-emerald-300 hover:shadow-lg transition-all duration-300">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                        <div class="flex-1 mb-4 md:mb-0">
                                            <!-- Service Icon -->
                                            <div class="flex items-start space-x-4">
                                                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                                    <i class="fas fa-spa text-emerald-600"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-xl font-semibold text-gray-800 mb-2 group-hover:text-emerald-600 transition-colors">
                                                        {{ $masterService->service->name }}
                                                    </h3>
                                                    <p class="text-gray-600 mb-3 leading-relaxed">
                                                        {{ $masterService->service->description }}
                                                    </p>
                                                    
                                                    <!-- Service Details -->
                                                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                                        <span class="flex items-center">
                                                            <i class="fas fa-clock mr-1 text-emerald-500"></i>
                                                            {{ $masterService->getDuration() }} хвилин
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-users mr-1 text-emerald-500"></i>
                                                            Індивідуально
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Price & Booking -->
                                        <div class="text-center md:text-right md:ml-6">
                                            <div class="mb-4">
                                                <div class="text-3xl font-bold text-emerald-600">
                                                    {{ number_format($masterService->price, 0) }}
                                                    <span class="text-lg text-gray-500">грн</span>
                                                </div>
                                                <div class="text-sm text-gray-500">за сеанс</div>
                                            </div>
                                            
                                            <a href="{{ route('appointment.create', ['master_id' => $master->id, 'service_id' => $masterService->service->id]) }}" 
                                               class="inline-block bg-emerald-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                                <i class="fas fa-calendar-plus mr-2"></i>
                                                Записатися
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-circle text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">Послуги наразі не налаштовані</p>
                        </div>
                    @endif
                </div>
            </section>

            
            <!-- Experience & Certifications -->
            <section class="mb-12">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Досвід та кваліфікація</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Experience -->
                        <div class="bg-white rounded-lg p-6 text-center shadow-md">
                            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user-graduate text-2xl text-emerald-600"></i>
                            </div>
                            <h4 class="font-bold text-xl text-gray-800 mb-2">{{ $master->experience_years ?? 5 }}+</h4>
                            <p class="text-gray-600">років досвіду</p>
                        </div>
                        
                        <!-- Clients -->
                        <div class="bg-white rounded-lg p-6 text-center shadow-md">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-2xl text-blue-600"></i>
                            </div>
                            <h4 class="font-bold text-xl text-gray-800 mb-2">{{ $master->clients_count ?? 200 }}+</h4>
                            <p class="text-gray-600">задоволених клієнтів</p>
                        </div>
                        
                        <!-- Certifications -->
                        <div class="bg-white rounded-lg p-6 text-center shadow-md">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-certificate text-2xl text-yellow-600"></i>
                            </div>
                            <h4 class="font-bold text-xl text-gray-800 mb-2">{{ $master->certificates_count ?? 3 }}+</h4>
                            <p class="text-gray-600">сертифікатів</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Floating Book Button (Mobile) -->
<div class="fixed bottom-6 right-6 md:hidden z-40">
    <a href="#services" 
       class="bg-emerald-600 text-white p-4 rounded-full shadow-2xl hover:bg-emerald-700 transition-all duration-300 hover:scale-110">
        <i class="fas fa-calendar-plus text-xl"></i>
    </a>
</div>

@push('styles')
<style>
    .sticky {
        position: sticky;
    }
    
    @media (max-width: 1024px) {
        .sticky {
            position: relative;
        }
    }
</style>
@endpush
@endsection