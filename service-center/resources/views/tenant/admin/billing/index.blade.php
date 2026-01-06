@extends('layouts.admin')

@section('title', 'Біллінг')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Біллінг та підписка</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-medium mb-4">Поточний план</h2>

        @if($tenant->isOnTrial())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium text-yellow-800">
                        Пробний період до {{ $tenant->trial_ends_at->format('d.m.Y') }}
                        (залишилось {{ $tenant->trial_ends_at->diffInDays(now()) }} днів)
                    </span>
                </div>
            </div>
        @endif

        @if($tenant->subscription && $tenant->subscription->isActive())
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium text-green-800">Активна підписка</span>
                        <p class="text-sm text-green-600 mt-1">
                            Наступне списання: {{ $tenant->subscription->current_period_end?->format('d.m.Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-green-800">
                            {{ $tenant->masterCount() }} майстрів
                        </span>
                        <p class="text-sm text-green-600">
                            ${{ $tenant->masterCount() * 10 }}/місяць
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-gray-600">Підписка не активна</p>
                <button class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Оформити підписку
                </button>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium mb-4">Статистика використання</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">Майстрів</div>
                <div class="text-2xl font-bold text-gray-900">{{ $tenant->masterCount() }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">Клієнтів</div>
                <div class="text-2xl font-bold text-gray-900">{{ $tenant->clients()->count() }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-500">Записів цього місяця</div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ $tenant->appointments()->whereMonth('created_at', now()->month)->count() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
