@extends('layouts.admin')

@section('title', 'Послуги')
@section('page-title', 'Управління послугами')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <h3 class="text-lg font-semibold flex-grow">Список послуг</h3>
            <a href="{{ route('admin.services.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Додати послугу
            </a>
        </div>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Тривалість</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Майстрів</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($services as $service)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-pink-100 rounded-full flex items-center justify-center mr-3">
                                    @if($service->photo)
                                        <img src="{{ asset('storage/' . $service->photo) }}" 
                                             alt="{{ $service->name }}" 
                                             class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <i class="fas fa-spa text-pink-600"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $service->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden md:table-cell">
                            <span class="inline-flex items-center">
                                <i class="fas fa-clock text-gray-400 mr-1"></i>
                                {{ $service->duration }} хв
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-users mr-1"></i>
                                {{ $service->master_services_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($service->is_active)
                                <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Активна
                                </span>
                            @else
                                <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Неактивна
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('admin.services.edit', $service->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                   title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.services.destroy', $service->id) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені? Це видалить послугу та всі пов\'язані записи!')">
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
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-spa text-4xl mb-3 text-gray-400"></i>
                            <p>Послуг не знайдено</p>
                            <a href="{{ route('admin.services.create') }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i>Додати першу послугу
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($services as $service)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 bg-pink-100 rounded-full flex items-center justify-center">
                            @if($service->photo)
                                <img src="{{ asset('storage/' . $service->photo) }}" 
                                     alt="{{ $service->name }}" 
                                     class="h-10 w-10 rounded-full object-cover">
                            @else
                                <i class="fas fa-spa text-pink-600"></i>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ $service->name }}</h3>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.services.edit', $service->id) }}" 
                           class="text-indigo-600"
                           title="Редагувати">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.services.destroy', $service->id) }}" 
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
                        <span class="text-gray-600">Тривалість:</span>
                        <span>{{ $service->duration }} хв</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Майстрів:</span>
                        <span>{{ $service->master_services_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Статус:</span>
                        @if($service->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                Активна
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                Неактивна
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-spa text-4xl mb-3 text-gray-400"></i>
                <p>Послуг не знайдено</p>
                <a href="{{ route('admin.services.create') }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                    <i class="fas fa-plus mr-1"></i>Додати першу послугу
                </a>
            </div>
        @endforelse
    </div>

    @if($services->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $services->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Додаткові інтерактивні функції можна додати сюди
    document.addEventListener('DOMContentLoaded', function() {
        // Приклад: підтвердження видалення
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