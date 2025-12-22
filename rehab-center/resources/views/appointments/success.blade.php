@extends('layouts.app')

@section('title', 'Запис підтверджено')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="mb-6">
            <i class="fas fa-check-circle text-6xl text-green-500"></i>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-4">Запис підтверджено!</h1>
        <p class="text-gray-600 mb-8">Дякуємо за ваш запис. Ми зв'яжемося з вами найближчим часом для підтвердження.</p>

        @if($appointment)
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="font-semibold mb-4">Деталі вашого запису:</h3>
                <div class="space-y-2 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Спеціаліст:</span>
                        <span class="font-semibold">{{ $appointment->master->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Послуга:</span>
                        <span class="font-semibold">{{ $appointment->service->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Дата:</span>
                        <span class="font-semibold">{{ $appointment->appointment_date->format('d.m.Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Час:</span>
                        <span class="font-semibold">{{ substr($appointment->appointment_time, 0, 5) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Вартість:</span>
                        <span class="font-semibold text-green-600">{{ number_format($appointment->price, 0) }} грн</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-4">
            <a href="{{ route('home') }}"
               class="inline-block bg-pink-500 text-white px-6 py-3 rounded-lg hover:bg-pink-700 transition duration-300">
                На головну
            </a>

            @if($appointment && $appointment->canBeCancelled())
                <form method="POST" action="{{ route('appointment.cancel', $appointment->id) }}" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300"
                            onclick="return confirm('Ви впевнені, що хочете скасувати запис?')">
                        Скасувати запис
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
