@extends('platform.layouts.app')

@section('title', 'ServiceCenter - SaaS платформа для сервісних центрів')

@section('content')
<div class="relative bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="text-center">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block">Система онлайн-записів</span>
                        <span class="block text-indigo-600">для вашого бізнесу</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl">
                        Керуйте записами клієнтів, розкладом майстрів та отримуйте онлайн-платежі.
                        Все в одному місці.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center">
                        <div class="rounded-md shadow">
                            <a href="{{ route('platform.register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                                Спробувати безкоштовно
                            </a>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <a href="{{ route('platform.features') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                                Дізнатися більше
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Все що потрібно для вашого бізнесу
            </h2>
        </div>
        <div class="mt-10">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-indigo-600 text-3xl mb-4">📅</div>
                    <h3 class="text-lg font-medium text-gray-900">Онлайн-запис</h3>
                    <p class="mt-2 text-gray-500">
                        Клієнти можуть записуватися 24/7 через ваш персональний сайт
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-indigo-600 text-3xl mb-4">👥</div>
                    <h3 class="text-lg font-medium text-gray-900">Управління персоналом</h3>
                    <p class="mt-2 text-gray-500">
                        Керуйте розкладом майстрів, їх послугами та цінами
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-indigo-600 text-3xl mb-4">📱</div>
                    <h3 class="text-lg font-medium text-gray-900">Telegram сповіщення</h3>
                    <p class="mt-2 text-gray-500">
                        Автоматичні нагадування для клієнтів та майстрів
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="bg-indigo-800">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-xl text-indigo-200">Нам довіряють</p>
            <p class="text-5xl font-extrabold text-white mt-2">{{ $tenantsCount }}+</p>
            <p class="text-xl text-indigo-200 mt-1">організацій</p>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-white">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
        <div class="bg-indigo-700 rounded-lg shadow-xl overflow-hidden">
            <div class="px-6 py-12 sm:px-12">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-white">
                        Готові почати?
                    </h2>
                    <p class="mt-4 text-lg text-indigo-200">
                        14 днів безкоштовно. Без кредитної картки.
                    </p>
                    <a href="{{ route('platform.register') }}" class="mt-8 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                        Створити акаунт
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
