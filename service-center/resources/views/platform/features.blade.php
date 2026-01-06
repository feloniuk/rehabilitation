@extends('platform.layouts.app')

@section('title', 'Можливості - ServiceCenter')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">Можливості платформи</h1>
            <p class="mt-4 text-xl text-gray-600">
                Все що потрібно для ефективного управління вашим бізнесом
            </p>
        </div>

        <div class="mt-16 space-y-16">
            <!-- Feature 1 -->
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="lg:w-1/2">
                    <h2 class="text-2xl font-bold text-gray-900">Онлайн-запис 24/7</h2>
                    <p class="mt-4 text-gray-600">
                        Ваші клієнти можуть записуватися на послуги в будь-який час, навіть коли ви відпочиваєте.
                        Зручний інтерфейс вибору послуги, майстра, дати та часу.
                    </p>
                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Вибір зручного часу
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Автоматичне підтвердження
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Можливість скасування
                        </li>
                    </ul>
                </div>
                <div class="lg:w-1/2 bg-gray-100 rounded-lg p-8">
                    <div class="text-6xl text-center">📅</div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="flex flex-col lg:flex-row-reverse items-center gap-8">
                <div class="lg:w-1/2">
                    <h2 class="text-2xl font-bold text-gray-900">Управління персоналом</h2>
                    <p class="mt-4 text-gray-600">
                        Додавайте майстрів, налаштовуйте їх розклад роботи, послуги та індивідуальні ціни.
                        Кожен майстер бачить тільки свої записи.
                    </p>
                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Гнучкий розклад роботи
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Індивідуальні ціни
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Профілі майстрів
                        </li>
                    </ul>
                </div>
                <div class="lg:w-1/2 bg-gray-100 rounded-lg p-8">
                    <div class="text-6xl text-center">👥</div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="lg:w-1/2">
                    <h2 class="text-2xl font-bold text-gray-900">Telegram сповіщення</h2>
                    <p class="mt-4 text-gray-600">
                        Автоматичні нагадування для клієнтів та майстрів про майбутні записи.
                        Налаштовуйте шаблони повідомлень під свій бренд.
                    </p>
                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Нагадування про запис
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Сповіщення для майстрів
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Кастомізація шаблонів
                        </li>
                    </ul>
                </div>
                <div class="lg:w-1/2 bg-gray-100 rounded-lg p-8">
                    <div class="text-6xl text-center">📱</div>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="flex flex-col lg:flex-row-reverse items-center gap-8">
                <div class="lg:w-1/2">
                    <h2 class="text-2xl font-bold text-gray-900">Зручний календар</h2>
                    <p class="mt-4 text-gray-600">
                        Візуальний календар всіх записів з можливістю швидкого перегляду та редагування.
                        Фільтрація по майстрах та статусах.
                    </p>
                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Візуальний календар
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Фільтрація записів
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Швидке редагування
                        </li>
                    </ul>
                </div>
                <div class="lg:w-1/2 bg-gray-100 rounded-lg p-8">
                    <div class="text-6xl text-center">📊</div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="mt-16 text-center">
            <a href="{{ route('platform.register') }}"
               class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Почати безкоштовно
            </a>
            <p class="mt-3 text-sm text-gray-500">
                14 днів безкоштовно. Без кредитної картки.
            </p>
        </div>
    </div>
</div>
@endsection
