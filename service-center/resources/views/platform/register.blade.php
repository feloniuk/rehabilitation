@extends('platform.layouts.app')

@section('title', 'Реєстрація - ServiceCenter')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Створити акаунт
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                14 днів безкоштовно. Без кредитної картки.
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('platform.register.store') }}" method="POST">
            @csrf

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Назва компанії</label>
                    <input id="company_name" name="company_name" type="text" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Наприклад: Салон краси 'Гармонія'"
                           value="{{ old('company_name') }}">
                </div>

                <div>
                    <label for="owner_name" class="block text-sm font-medium text-gray-700">Ваше ім'я</label>
                    <input id="owner_name" name="owner_name" type="text" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Іван Петренко"
                           value="{{ old('owner_name') }}">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="email@example.com"
                           value="{{ old('email') }}">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Телефон</label>
                    <input id="phone" name="phone" type="text" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="+380XXXXXXXXX"
                           value="{{ old('phone') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Мінімум 8 символів">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Підтвердіть пароль</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Повторіть пароль">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Створити акаунт
                </button>
            </div>

            <p class="text-center text-sm text-gray-600">
                Вже маєте акаунт?
                <a href="{{ route('platform.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Увійти
                </a>
            </p>
        </form>
    </div>
</div>
@endsection
