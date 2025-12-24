{{-- resources/views/admin/master-notification-logs/show.blade.php --}}

@extends('layouts.admin')

@section('title', 'Просмотр лога отправки')
@section('page-title', 'Лог отправки уведомления #' . $log->id)

@section('content')
<div class="max-w-4xl">
    <div class="grid gap-6">
        {{-- Статус отправки --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Статус отправки</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="border-l-4 {{ $log->status === 'sent' ? 'border-green-500' : 'border-gray-300' }} pl-4">
                    <div class="text-sm text-gray-600">Статус</div>
                    <div class="text-lg font-semibold">
                        @if($log->status === 'sent')
                            <span class="text-green-600"><i class="fas fa-check mr-1"></i>Отправлено</span>
                        @elseif($log->status === 'failed')
                            <span class="text-red-600"><i class="fas fa-times mr-1"></i>Ошибка</span>
                        @else
                            <span class="text-yellow-600"><i class="fas fa-clock mr-1"></i>Ожидание</span>
                        @endif
                    </div>
                </div>

                <div class="border-l-4 border-blue-500 pl-4">
                    <div class="text-sm text-gray-600">Источник</div>
                    <div class="text-lg font-semibold text-blue-600">
                        {{ $log->resolution_source ? ucfirst($log->resolution_source) : '—' }}
                    </div>
                </div>

                <div class="border-l-4 border-purple-500 pl-4">
                    <div class="text-sm text-gray-600">Chat ID</div>
                    <div class="text-lg font-semibold text-purple-600">
                        {{ $log->chat_id ?? '—' }}
                    </div>
                </div>

                <div class="border-l-4 border-gray-500 pl-4">
                    <div class="text-sm text-gray-600">Время</div>
                    <div class="text-lg font-semibold">{{ $log->created_at->format('d.m.Y H:i:s') }}</div>
                </div>
            </div>
        </div>

        {{-- Информация о мастере --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-user-md text-blue-500 mr-2"></i>Информация о мастере
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-600">Имя</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $log->master->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Телефон</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $log->phone }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Email</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $log->master->email }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Статус</div>
                    @if($log->master->is_active)
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                            Активен
                        </span>
                    @else
                        <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
                            Неактивен
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Информация о записи --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-calendar text-green-500 mr-2"></i>Информация о записи
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-600">Клиент</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $log->appointment->client->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Услуга</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $log->appointment->service->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Дата и время</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $log->appointment->appointment_date->format('d.m.Y') }}
                        в {{ substr($log->appointment->appointment_time, 0, 5) }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Длительность</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $log->appointment->duration }} мин</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Цена</div>
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($log->appointment->price, 2) }} грн</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Статус записи</div>
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                        {{ ucfirst($log->appointment->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Текст уведомления --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-comment text-purple-500 mr-2"></i>Текст уведомления
            </h2>

            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 font-mono text-sm text-gray-700 whitespace-pre-wrap break-words">
                {{ $log->message }}
            </div>
        </div>

        {{-- Ошибка если была --}}
        @if($log->error_message)
            <div class="bg-red-50 rounded-lg shadow p-6 border-l-4 border-red-500">
                <h2 class="text-xl font-semibold mb-4 text-red-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Ошибка при отправке
                </h2>

                <div class="bg-white rounded p-3 border border-red-200 font-mono text-sm text-red-700 overflow-auto max-h-64">
                    {{ $log->error_message }}
                </div>
            </div>
        @endif

        {{-- Дополнительная информация --}}
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Метаданные</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID уведомления:</span>
                    <span class="text-gray-900 font-mono">{{ $log->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">ID записи:</span>
                    <span class="text-gray-900 font-mono">{{ $log->appointment_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Создано:</span>
                    <span class="text-gray-900">{{ $log->created_at->format('d.m.Y H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Обновлено:</span>
                    <span class="text-gray-900">{{ $log->updated_at->format('d.m.Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        {{-- Действия --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.master-notification-logs.index') }}"
               class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Вернуться к логам
            </a>
            <a href="{{ route('admin.appointments.index') }}"
               class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-calendar mr-2"></i>Просмотреть запись
            </a>
        </div>
    </div>
</div>
@endsection
