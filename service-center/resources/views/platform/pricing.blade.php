@extends('platform.layouts.app')

@section('title', 'Ціни - ServiceCenter')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">Прості та прозорі ціни</h1>
            <p class="mt-4 text-xl text-gray-600">
                Платіть лише за активних майстрів. Без прихованих платежів.
            </p>
        </div>

        <div class="mt-16 flex justify-center">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden max-w-lg w-full">
                <div class="px-6 py-8">
                    <h2 class="text-2xl font-bold text-gray-900 text-center">За майстра</h2>
                    <div class="mt-4 flex justify-center items-baseline">
                        <span class="text-5xl font-extrabold text-indigo-600">$10</span>
                        <span class="ml-1 text-xl text-gray-500">/місяць</span>
                    </div>
                    <p class="mt-2 text-center text-gray-500">за кожного активного майстра</p>
                </div>

                <div class="border-t border-gray-200 px-6 py-8">
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Необмежена кількість клієнтів</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Необмежена кількість записів</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Telegram сповіщення</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Персональний сайт для запису</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Управління розкладом</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Технічна підтримка</span>
                        </li>
                    </ul>
                </div>

                <div class="px-6 py-8 bg-gray-50">
                    <a href="{{ route('platform.register') }}"
                       class="block w-full text-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">
                        Спробувати 14 днів безкоштовно
                    </a>
                    <p class="mt-3 text-center text-sm text-gray-500">
                        Без кредитної картки
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center">
            <h3 class="text-lg font-medium text-gray-900">Потрібен особливий план?</h3>
            <p class="mt-2 text-gray-600">
                Зв'яжіться з нами для обговорення індивідуальних умов
            </p>
            <a href="mailto:support@servicecenter.com" class="mt-4 inline-block text-indigo-600 hover:text-indigo-500">
                support@servicecenter.com
            </a>
        </div>
    </div>
</div>
@endsection
