@extends('layouts.app')

@section('title', $service->name . ' - Послуги')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">Головна</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="{{ route('home') }}#services" class="hover:text-emerald-600 transition-colors">Послуги</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-gray-700">{{ $service->name }}</li>
        </ol>
    </nav>

    <!-- Service Header -->
    <div class="relative bg-gradient-to-r from-emerald-500 to-teal-600 rounded-3xl overflow-hidden mb-12">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="white" fill-opacity="0.4"><circle cx="30" cy="30" r="2"/></g></g></svg>')"></div>
        </div>
        
        <div class="relative px-8 py-16 md:px-12 md:py-20">
            <div class="max-w-4xl">
                <!-- Service Icon -->
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-spa text-3xl text-white"></i>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">{{ $service->name }}</h1>
                <p class="text-xl text-white/90 mb-8 leading-relaxed max-w-3xl">
                    {{ $service->description }}
                </p>
                
                <!-- Quick Stats -->
                <div class="flex flex-wrap gap-6 text-white">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-white/80 mr-2"></i>
                        <span class="font-medium">{{ $service->duration }} хвилин</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users text-white/80 mr-2"></i>
                        <span class="font-medium">{{ $masters->count() }} спеціалістів</span>
                    </div>
                    @php
                        $allPrices = $masters->flatMap(function($master) {
                            return $master->masterServices->pluck('price');
                        });
                        $minPrice = $allPrices->min();
                        $maxPrice = $allPrices->max();
                    @endphp
                    @if($minPrice)
                        <div class="flex items-center">
                            <i class="fas fa-tag text-white/80 mr-2"></i>
                            <span class="font-medium">
                                @if($minPrice == $maxPrice)
                                    {{ number_format($minPrice, 0) }} грн
                                @else
                                    від {{ number_format($minPrice, 0) }} грн
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Floating Elements -->
            <div class="absolute top-10 right-10 w-32 h-32 bg-white/5 rounded-full blur-2xl animate-pulse"></div>
            <div class="absolute bottom-20 right-32 w-20 h-20 bg-white/10 rounded-full blur-xl animate-bounce"></div>
        </div>
    </div>

    <!-- Service Benefits -->
    <section class="mb-12">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Переваги нашої послуги</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center group">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-200 transition-colors">
                        <i class="fas fa-user-md text-2xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Досвідчені спеціалісти</h3>
                    <p class="text-gray-600 leading-relaxed">Наші майстри мають багаторічний досвід та професійну підготовку</p>
                </div>
                
                <div class="text-center group">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-heart text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Індивідуальний підхід</h3>
                    <p class="text-gray-600 leading-relaxed">Кожна програма розробляється з урахуванням ваших потреб</p>
                </div>
                
                <div class="text-center group">
                    <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-teal-200 transition-colors">
                        <i class="fas fa-certificate text-2xl text-teal-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Гарантована якість</h3>
                    <p class="text-gray-600 leading-relaxed">Використовуємо сучасні методики та обладнання</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Masters Selection -->
    <section class="mb-12">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Оберіть спеціаліста</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Всі наші спеціалісти мають необхідну кваліфікацію та досвід для надання якісної послуги
                </p>
            </div>

            @if($masters->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($masters as $master)
                        @php
                            $masterService = $master->masterServices->first();
                        @endphp
                        <div class="group bg-gradient-to-br from-gray-50 to-white border-2 border-gray-100 rounded-2xl p-6 hover:border-emerald-300 hover:shadow-xl transition-all duration-300">
                            <!-- Master Photo -->
                            <div class="relative mb-6">
                                <div class="w-24 h-24 rounded-full overflow-hidden mx-auto border-4 border-emerald-100 group-hover:border-emerald-300 transition-colors">
                                    @if($master->photo)
                                        <img src="{{ asset('storage/' . $master->photo) }}"
                                             alt="{{ $master->name }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                            <i class="fas fa-user text-2xl text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Online Status -->
                                <div class="absolute bottom-0 right-1/2 transform translate-x-12 translate-y-1 w-6 h-6 bg-green-400 rounded-full border-4 border-white"></div>
                            </div>

                            <!-- Master Info -->
                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-emerald-600 transition-colors">
                                    {{ $master->name }}
                                </h3>
                                
                                <!-- Rating -->
                                <div class="flex justify-center mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    @endfor
                                    <span class="text-sm text-gray-500 ml-2">(4.9)</span>
                                </div>
                                
                                @if($master->description)
                                    <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                        {{ Str::limit($master->description, 100) }}
                                    </p>
                                @endif
                            </div>

                            <!-- Service Details -->
                            <div class="bg-white rounded-lg p-4 mb-6 shadow-sm">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm text-gray-600">Ціна сеансу</span>
                                    <span class="text-2xl font-bold text-emerald-600">
                                        {{ number_format($masterService->price, 0) }} 
                                        <span class="text-sm text-gray-500">грн</span>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Тривалість</span>
                                    <span class="text-sm font-medium text-gray-800">
                                        {{ $masterService->getDuration() }} хв
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                <a href="{{ route('appointment.create', ['master_id' => $master->id, 'service_id' => $service->id]) }}" 
                                   class="block w-full bg-emerald-600 text-white text-center px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-calendar-plus mr-2"></i>
                                    Записатися
                                </a>
                                
                                <a href="{{ route('masters.show', $master->id) }}" 
                                   class="block w-full border-2 border-emerald-600 text-emerald-600 text-center px-6 py-3 rounded-xl font-semibold hover:bg-emerald-600 hover:text-white transition-all duration-300">
                                    <i class="fas fa-user mr-2"></i>
                                    Детальніше
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-user-times text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Спеціалісти недоступні</h3>
                    <p class="text-gray-600 mb-6">На даний момент немає доступних спеціалістів для цієї послуги</p>
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Повернутися на головну
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Process Steps -->
    <section class="mb-12">
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Як проходить процедура</h2>
                <p class="text-lg text-gray-600">Простий процес запису та проведення сеансу</p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-8 md:space-y-0 md:space-x-8">
                    <!-- Step 1 -->
                    <div class="flex-1 text-center">
                        <div class="relative">
                            <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl font-bold text-white">1</span>
                            </div>
                            <div class="hidden md:block absolute top-8 left-full w-full h-0.5 bg-emerald-200"></div>
                        </div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">Запис онлайн</h3>
                        <p class="text-gray-600 text-sm">Оберіть спеціаліста та зручний час</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="flex-1 text-center">
                        <div class="relative">
                            <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl font-bold text-white">2</span>
                            </div>
                            <div class="hidden md:block absolute top-8 left-full w-full h-0.5 bg-emerald-200"></div>
                        </div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">Консультація</h3>
                        <p class="text-gray-600 text-sm">Обговорення ваших потреб та плану</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="flex-1 text-center">
                        <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-white">3</span>
                        </div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-2">Процедура</h3>
                        <p class="text-gray-600 text-sm">Проведення сеансу професійним спеціалістом</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="mb-12">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Часті питання</h2>
            
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full text-left p-6 focus:outline-none" onclick="toggleFaq(1)">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Скільки сеансів потрібно для результату?</h3>
                            <i class="fas fa-chevron-down text-gray-400 transform transition-transform" id="icon-1"></i>
                        </div>
                    </button>
                    <div class="hidden p-6 pt-0 text-gray-600" id="answer-1">
                        Кількість сеансів залежить від індивідуальних потреб та стану здоров'я. 
                        Зазвичай рекомендується курс з 5-10 сеансів для досягнення стабільного результату.
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full text-left p-6 focus:outline-none" onclick="toggleFaq(2)">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Чи можна скасувати запис?</h3>
                            <i class="fas fa-chevron-down text-gray-400 transform transition-transform" id="icon-2"></i>
                        </div>
                    </button>
                    <div class="hidden p-6 pt-0 text-gray-600" id="answer-2">
                        Так, ви можете скасувати запис не пізніше ніж за 24 години до призначеного часу. 
                        Для скасування зв'яжіться з нами по телефону.
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full text-left p-6 focus:outline-none" onclick="toggleFaq(3)">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Які протипоказання до процедури?</h3>
                            <i class="fas fa-chevron-down text-gray-400 transform transition-transform" id="icon-3"></i>
                        </div>
                    </button>
                    <div class="hidden p-6 pt-0 text-gray-600" id="answer-3">
                        Протипоказання обговорюються індивідуально на консультації. 
                        Загалом не рекомендується при гострих запальних процесах та деяких хронічних захворюваннях.
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function toggleFaq(id) {
    const answer = document.getElementById(`answer-${id}`);
    const icon = document.getElementById(`icon-${id}`);
    
    answer.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endpush
@endsection