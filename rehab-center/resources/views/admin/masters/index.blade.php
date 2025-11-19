@extends('layouts.admin')

@section('title', 'Майстри')
@section('page-title', 'Управління майстрами')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <h3 class="text-lg font-semibold flex-grow">Список майстрів</h3>
            <a href="{{ route('admin.masters.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Додати майстра
            </a>
        </div>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Майстер</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Послуги</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($masters as $master)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full mr-3">
                                    @if($master->photo)
                                        <img src="{{ asset('storage/' . $master->photo) }}" 
                                             alt="{{ $master->name }}" 
                                             class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-bold">{{ substr($master->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $master->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $master->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-concierge-bell mr-1"></i>
                                {{ $master->master_services_count }} послуг
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($master->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Активний
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Неактивний
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('admin.masters.show', $master->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors"
                                   title="Переглянути">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                                <a href="{{ route('admin.masters.edit', $master->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                   title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.masters.destroy', $master->id) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені?')">
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
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users-slash text-4xl mb-3 text-gray-400"></i>
                            <p>Майстрів не знайдено</p>
                            <a href="{{ route('admin.masters.create') }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i>Додати першого майстра
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($masters as $master)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full">
                            @if($master->photo)
                                <img src="{{ asset('storage/' . $master->photo) }}" 
                                     alt="{{ $master->name }}" 
                                     class="h-10 w-10 rounded-full object-cover">
                            @else
                                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-bold">{{ substr($master->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $master->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $master->email }}</p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.masters.edit', $master->id) }}" 
                           class="text-indigo-600"
                           title="Редагувати">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.masters.destroy', $master->id) }}" 
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
                        <span class="text-gray-600">Послуги:</span>
                        <span>{{ $master->master_services_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Статус:</span>
                        @if($master->is_active)
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
                <p>Майстрів не знайдено</p>
                <a href="{{ route('admin.masters.create') }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                    <i class="fas fa-plus mr-1"></i>Додати першого майстра
                </a>
            </div>
        @endforelse
    </div>

    @if($masters->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $masters->links('vendor.pagination.tailwind') }}
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