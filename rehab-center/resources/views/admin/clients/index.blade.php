@extends('layouts.admin')

@section('title', 'Клієнти')
@section('page-title', 'Управління клієнтами')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <h3 class="text-lg font-semibold flex-grow">Список клієнтів</h3>
            <a href="{{ route('admin.clients.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Додати клієнта
            </a>
        </div>
    </div>

    {{-- Фільтри --}}
    <div class="px-4 py-4 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Пошук за ім'ям, телефоном, email або Telegram..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Всі статуси</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активні</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Неактивні</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>Фільтр
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.clients.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>Скинути
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клієнт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Телефон</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telegram</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Записів</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-bold">
                                        {{ substr($client->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $client->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $client->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($client->telegram_username)
                                <a href="https://t.me/{{ $client->telegram_username }}" target="_blank"
                                   class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <i class="fab fa-telegram mr-1"></i>
                                    {{ '@' . $client->telegram_username }}
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                {{ $client->client_appointments_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($client->is_active)
                                <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Активний
                                </span>
                            @else
                                <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Неактивний
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('admin.clients.show', $client->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors"
                                   title="Деталі">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                                <a href="{{ route('admin.clients.edit', $client->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                   title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.clients.destroy', $client->id) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені? Це видалить всі записи клієнта!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="Видалити">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users-slash text-4xl mb-3 text-gray-400"></i>
                            <p>Клієнтів не знайдено</p>
                            <a href="{{ route('admin.clients.create') }}"
                               class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i>Додати першого клієнта
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($clients as $client)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold">
                                {{ substr($client->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $client->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $client->email }}</p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.clients.show', $client->id) }}" 
                           class="text-blue-600"
                           title="Деталі">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.clients.edit', $client->id) }}" 
                           class="text-indigo-600"
                           title="Редагувати">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.clients.destroy', $client->id) }}" 
                              class="inline" onsubmit="return confirm('Ви впевнені?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600"
                                    title="Видалити">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Телефон:</span>
                        <span>{{ $client->phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Telegram:</span>
                        @if($client->telegram_username)
                            <a href="https://t.me/{{ $client->telegram_username }}" target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fab fa-telegram mr-1"></i>{{ '@' . $client->telegram_username }}
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Записів:</span>
                        <span>{{ $client->client_appointments_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Статус:</span>
                        @if($client->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                Активний
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                Неактивний
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-users-slash text-4xl mb-3 text-gray-400"></i>
                <p>Клієнтів не знайдено</p>
                <a href="{{ route('admin.clients.create') }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                    <i class="fas fa-plus mr-1"></i>Додати першого клієнта
                </a>
            </div>
        @endforelse
    </div>

    @if($clients->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $clients->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('form[onsubmit]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const confirmed = confirm(this.getAttribute('onsubmit').replace('return ', ''));
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
@endsection