@extends('super-admin.layouts.app')

@section('title', 'Організації')
@section('page-title', 'Управління організаціями')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h2 class="text-3xl font-bold text-gray-800">Організації</h2>
    <a href="{{ route('super-admin.tenants.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        Створити організацію
    </a>
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <form method="GET" action="{{ route('super-admin.tenants.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Пошук</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ім'я або slug" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Всі статуси</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активні</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Неактивні</option>
                <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-search mr-2"></i>
                Пошук
            </button>
        </div>
    </form>
</div>

<!-- Tenants Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Ім'я</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Власник</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Підписка</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Статус</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
                <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                            <p class="text-sm text-gray-500">{{ $tenant->slug }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($tenant->owner)
                            <div>
                                <p class="font-medium text-gray-900">{{ $tenant->owner->name }}</p>
                                <p class="text-sm text-gray-500">{{ $tenant->owner->email }}</p>
                            </div>
                        @else
                            <span class="text-gray-500 text-sm">Немає власника</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($tenant->subscription)
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full @if($tenant->subscription->isActive()) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($tenant->subscription->status) }}
                            </span>
                        @elseif($tenant->isOnTrial())
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Trial до {{ $tenant->trial_ends_at->format('d.m.Y') }}
                            </span>
                        @else
                            <span class="text-gray-500 text-sm">Немає</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($tenant->is_active)
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>
                                Активна
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>
                                Неактивна
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-blue-600 hover:text-blue-900" title="Переглянути">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="text-amber-600 hover:text-amber-900" title="Редагувати">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($tenant->owner)
                                <form action="{{ route('super-admin.tenants.impersonate', $tenant) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-purple-600 hover:text-purple-900" title="Увійти як власник">
                                        <i class="fas fa-user-secret"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('super-admin.tenants.toggleStatus', $tenant) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900" title="Змінити статус">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 block opacity-50"></i>
                        Організації не знайдені
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $tenants->links() }}
</div>
@endsection
