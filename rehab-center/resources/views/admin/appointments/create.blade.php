@extends('layouts.admin')

@section('title', 'Створити запис')
@section('page-title', 'Ручне створення запису')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 5px 12px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px;
    color: #111827;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
}
.select2-dropdown {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
}
.select2-results__option {
    padding: 10px;
}
</style>
@endpush

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.appointments.manual.store') }}" id="appointment-form">
            @csrf

            {{-- Майстер та послуга --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="master_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-md text-blue-500 mr-1"></i>
                        Майстер *
                    </label>
                    <select id="master_id" name="master_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Оберіть майстра</option>
                        @foreach($masters as $master)
                            <option value="{{ $master->id }}">{{ $master->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-spa text-green-500 mr-1"></i>
                        Послуга *
                    </label>
                    <select id="service_id" name="service_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Оберіть послугу</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration }} хв)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Дата, час, тривалість --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar text-purple-500 mr-1"></i>
                        Дата *
                    </label>
                    <input type="date" id="appointment_date" name="appointment_date" required 
                           value="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock text-orange-500 mr-1"></i>
                        Час *
                    </label>
                    <input type="time" id="appointment_time" name="appointment_time" required 
                           value="09:00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-teal-500 mr-1"></i>
                        Тривалість (хв) *
                    </label>
                    <input type="number" id="duration" name="duration" required min="15" step="15" value="60"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Ціна --}}
            <div class="mb-6">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                    Ціна (грн) *
                </label>
                <input type="number" id="price" name="price" required min="0" step="0.01"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Оберіть майстра та послугу">
            </div>

            {{-- Вибір клієнта --}}
            <div class="mb-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    Клієнт
                </h3>

                <div class="mb-4">
                    <label class="inline-flex items-center mr-6">
                        <input type="radio" name="client_type" value="existing" checked class="mr-2">
                        <span class="text-sm">Існуючий клієнт</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="client_type" value="new" class="mr-2">
                        <span class="text-sm">Новий клієнт</span>
                    </label>
                </div>

                {{-- Існуючий клієнт з Select2 --}}
                <div id="existing-client-block">
                    <label for="existing_client" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>
                        Пошук клієнта (введіть мінімум 2 символи)
                    </label>
                    <select id="existing_client" name="existing_client" class="w-full">
                        <option value=""></option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Введіть ім'я або телефон для пошуку. Результати підвантажуються автоматично.
                    </p>
                </div>

                {{-- Новий клієнт --}}
                <div id="new-client-block" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_client_name" class="block text-sm font-medium text-gray-700 mb-2">Ім'я *</label>
                            <input type="text" id="new_client_name" name="new_client_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="new_client_phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
                            <input type="tel" id="new_client_phone" name="new_client_phone"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="+380 XX XXX XX XX">
                        </div>

                        <div class="md:col-span-2">
                            <label for="new_client_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="new_client_email" name="new_client_email"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Примітки --}}
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-yellow-500 mr-1"></i>
                    Примітки
                </label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Додаткова інформація..."></textarea>
            </div>

            {{-- Дозвіл на нахлест --}}
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <label class="flex items-center">
                    <input type="checkbox" name="allow_overlap" value="1" class="mr-3 w-4 h-4">
                    <span class="text-sm">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                        <strong>Дозволити створення запису навіть якщо час зайнятий</strong>
                    </span>
                </label>
            </div>

            {{-- Кнопки --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.appointments.index') }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Скасувати
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Створити запис
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Select2 для клієнтів
    $('#existing_client').select2({
        ajax: {
            url: '{{ route("admin.appointments.search-clients") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: 'Введіть ім\'я або телефон...',
        minimumInputLength: 2,
        allowClear: true,
        language: {
            inputTooShort: function() {
                return 'Введіть мінімум 2 символи';
            },
            searching: function() {
                return 'Пошук...';
            },
            noResults: function() {
                return 'Нічого не знайдено';
            },
            loadingMore: function() {
                return 'Завантаження...';
            }
        }
    });

    // Перемикання типу клієнта
    $('input[name="client_type"]').on('change', function() {
        if ($(this).val() === 'existing') {
            $('#existing-client-block').show();
            $('#new-client-block').hide();
            $('#existing_client').prop('required', true);
            $('#new_client_name, #new_client_phone').prop('required', false);
        } else {
            $('#existing-client-block').hide();
            $('#new-client-block').show();
            $('#existing_client').prop('required', false);
            $('#new_client_name, #new_client_phone').prop('required', true);
        }
    });

    // Автозаповнення ціни
    $('#master_id, #service_id').on('change', function() {
        const masterId = $('#master_id').val();
        const serviceId = $('#service_id').val();

        if (masterId && serviceId) {
            $.get('/admin/appointments/get-service-price', {
                master_id: masterId,
                service_id: serviceId
            }).done(function(data) {
                $('#price').val(data.price);
                $('#duration').val(data.duration);
            });
        }
    });

    // Форматування телефону
    $('#new_client_phone').on('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('380')) value = value.substring(3);
        if (value.length > 0) {
            value = '+380 ' + value.replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
        }
        e.target.value = value.trim();
    });
});
</script>
@endpush
@endsection