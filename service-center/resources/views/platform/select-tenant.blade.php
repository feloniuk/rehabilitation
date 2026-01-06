@extends('platform.layouts.app')

@section('title', 'Оберіть організацію - ServiceCenter')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Оберіть організацію
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ви маєте доступ до декількох організацій
            </p>
        </div>

        <div class="space-y-4">
            @foreach($tenants as $tenant)
                <a href="{{ route('tenant.admin.dashboard', ['tenant' => $tenant->slug]) }}"
                   class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $tenant->name }}</h3>
                            <p class="text-sm text-gray-500">
                                Роль: {{ ucfirst(auth()->user()->roleInTenant($tenant)) }}
                            </p>
                        </div>
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ route('platform.register') }}" class="text-indigo-600 hover:text-indigo-500">
                Створити нову організацію
            </a>
        </div>
    </div>
</div>
@endsection
