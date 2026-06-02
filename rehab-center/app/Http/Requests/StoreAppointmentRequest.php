<?php

namespace App\Http\Requests;

use App\Helpers\PhoneHelper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    $normalized = PhoneHelper::normalize($value);
                    if (! $normalized || strlen($normalized) !== 13 || ! str_starts_with($normalized, '+380')) {
                        $fail('Введіть коректний номер телефону у форматі +380XXXXXXXXX.');
                    }
                },
            ],
            'master_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => [
                'required',
                Rule::in($this->getAvailableSlots()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введіть ваше ім\'я.',
            'phone.required' => 'Введіть номер телефону.',
            'phone.max' => 'Введіть коректний номер телефону у форматі +380XXXXXXXXX.',
            'appointment_date.required' => 'Оберіть дату.',
            'appointment_date.after_or_equal' => 'Дата не може бути в минулому.',
            'appointment_time.required' => 'Оберіть час.',
            'appointment_time.in' => 'Вибраний час недоступний. Оберіть час з запропонованих варіантів.',
        ];
    }

    private function getAvailableSlots(): array
    {
        $masterId = $this->input('master_id');
        $serviceId = $this->input('service_id');
        $date = $this->input('appointment_date');

        if (! $masterId || ! $serviceId || ! $date) {
            return [];
        }

        try {
            $controller = new \App\Http\Controllers\MasterController;
            $response = $controller->getAvailableSlots($masterId, $date, $serviceId);
            $slots = json_decode($response->getContent(), true);

            return is_array($slots) ? $slots : [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
