<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['name', 'message', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Доступні плейсхолдери для заміни
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            '{client_name}' => 'Ім\'я клієнта',
            '{master_name}' => 'Ім\'я майстра',
            '{service_name}' => 'Назва послуги',
            '{date}' => 'Дата запису (дд.мм.рррр)',
            '{time}' => 'Час запису (гг:хх)',
            '{duration}' => 'Тривалість (хв)',
            '{price}' => 'Вартість (грн)',
            '{center_name}' => 'Назва центру',
            '{center_phone}' => 'Телефон центру',
            '{center_address}' => 'Адреса центру',
        ];
    }

    /**
     * Замінити плейсхолдери на реальні дані
     */
    public function render(Appointment $appointment): string
    {
        $replacements = [
            '{client_name}' => $appointment->client->name,
            '{master_name}' => $appointment->master->name,
            '{service_name}' => $appointment->service->name,
            '{date}' => $appointment->appointment_date->format('d.m.Y'),
            '{time}' => substr($appointment->appointment_time, 0, 5),
            '{duration}' => $appointment->duration . ' хв',
            '{price}' => number_format($appointment->price, 0) . ' грн',
            '{center_name}' => Setting::get('center_name', 'Реабілітаційний центр'),
            '{center_phone}' => Setting::get('center_phone', ''),
            '{center_address}' => Setting::get('center_address', ''),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->message
        );
    }

    public function logs()
    {
        return $this->hasMany(NotificationLog::class, 'template_id');
    }
}