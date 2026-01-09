@extends('super-admin.layouts.app')

@section('title', $tenant->name)
@section('page-title', 'Деталі організації')

@section('content')
<div class="mb-6">
    <a href="{{ route('super-admin.tenants.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Повернутися до списку
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Main Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $tenant->name }}</h1>
                    <p class="text-gray-600">Slug: <code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code></p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">
                        <i class="fas fa-edit mr-2"></i>
                        Редагувати
                    </a>
                    @if($tenant->owner)
                        <form action="{{ route('super-admin.tenants.impersonate', $tenant) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                <i class="fas fa-user-secret mr-2"></i>
                                Увійти як власник
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 py-4 border-t border-b border-gray-200">
                <div>
                    <p class="text-sm text-gray-600">Власник</p>
                    @if($tenant->owner)
                        <p class="font-semibold text-gray-900">{{ $tenant->owner->name }}</p>
                        <p class="text-sm text-gray-600">{{ $tenant->owner->email }}</p>
                    @else
                        <p class="text-gray-500">Не вказано</p>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-600">Статус</p>
                    @if($tenant->is_active)
                        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 font-semibold rounded-full">
                            <i class="fas fa-check mr-2"></i>
                            Активна
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 font-semibold rounded-full">
                            <i class="fas fa-times mr-2"></i>
                            Неактивна
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 py-4 border-b border-gray-200">
                <div>
                    <p class="text-sm text-gray-600">Дата створення</p>
                    <p class="font-semibold text-gray-900">{{ $tenant->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Останнє оновлення</p>
                    <p class="font-semibold text-gray-900">{{ $tenant->updated_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>

            @if($tenant->subscription)
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-2">Підписка</p>
                    <div class="bg-gray-50 rounded p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-600">Статус</p>
                                <p class="font-semibold text-gray-900">{{ ucfirst($tenant->subscription->status) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Кількість мастерів</p>
                                <p class="font-semibold text-gray-900">{{ $tenant->subscription->quantity }}</p>
                            </div>
                            @if($tenant->subscription->current_period_end)
                                <div>
                                    <p class="text-xs text-gray-600">Період закінчується</p>
                                    <p class="font-semibold text-gray-900">{{ $tenant->subscription->current_period_end->format('d.m.Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($tenant->isOnTrial())
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded p-4">
                    <p class="text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Trial період
                    </p>
                    <p class="text-blue-800">Закінчується: {{ $tenant->trial_ends_at->format('d.m.Y H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div>
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Статистика</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded">
                    <span class="text-gray-700 flex items-center">
                        <i class="fas fa-users mr-2 text-blue-600"></i>
                        Користувачі
                    </span>
                    <span class="text-2xl font-bold text-blue-600">{{ $stats['users_count'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-purple-50 rounded">
                    <span class="text-gray-700 flex items-center">
                        <i class="fas fa-user-tie mr-2 text-purple-600"></i>
                        Мастери
                    </span>
                    <span class="text-2xl font-bold text-purple-600">{{ $stats['masters_count'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded">
                    <span class="text-gray-700 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-green-600"></i>
                        Клієнти
                    </span>
                    <span class="text-2xl font-bold text-green-600">{{ $stats['clients_count'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-amber-50 rounded">
                    <span class="text-gray-700 flex items-center">
                        <i class="fas fa-concierge-bell mr-2 text-amber-600"></i>
                        Послуги
                    </span>
                    <span class="text-2xl font-bold text-amber-600">{{ $stats['services_count'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-red-50 rounded">
                    <span class="text-gray-700 flex items-center">
                        <i class="fas fa-calendar-check mr-2 text-red-600"></i>
                        Записи
                    </span>
                    <span class="text-2xl font-bold text-red-600">{{ $stats['appointments_count'] }}</span>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-4">Небезпечна зона</h3>
            <form action="{{ route('super-admin.tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('Ви впевнені? Це видалить організацію безповоротно.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash mr-2"></i>
                    Видалити організацію
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Користувачі організації</h3>
    @if($tenant->users->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Ім'я</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Роль</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Дата приєднання</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenant->users as $user)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full @if($user->pivot->role === 'owner') bg-red-100 text-red-800 @elseif($user->pivot->role === 'admin') bg-blue-100 text-blue-800 @elseif($user->pivot->role === 'master') bg-purple-100 text-purple-800 @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($user->pivot->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->pivot->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 text-center py-8">Немає користувачів</p>
    @endif
</div>
@endsection
