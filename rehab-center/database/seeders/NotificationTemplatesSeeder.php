<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class NotificationTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Нагадування за 1 день',
                'message' => "Доброго дня, {client_name}! 👋

Нагадуємо про ваш запис:
📅 Дата: {date}
🕐 Час: {time}
👨‍⚕️ Майстер: {master_name}
💆 Послуга: {service_name}
⏱ Тривалість: {duration}

📍 Адреса: {center_address}
📞 Телефон: {center_phone}

Чекаємо на вас!
{center_name}",
                'is_active' => true,
            ],
            [
                'name' => 'Нагадування за 3 години',
                'message' => "Привіт, {client_name}! ⏰

Через 3 години у вас запис:
🕐 {time}
👨‍⚕️ {master_name}
💆 {service_name}

📍 {center_address}

До зустрічі!",
                'is_active' => true,
            ],
            [
                'name' => 'Подяка після візиту',
                'message' => "Дякуємо, {client_name}! 🙏

Сподіваємося, що ви залишились задоволені нашою послугою.

💆 {service_name}
👨‍⚕️ Майстер: {master_name}

Будемо раді бачити вас знову!
📞 {center_phone}

{center_name}",
                'is_active' => true,
            ],
            [
                'name' => 'Скасування запису',
                'message' => "Шановний(а) {client_name},

На жаль, ваш запис на {date} о {time} скасовано.

Якщо ви хочете перенести візит, зателефонуйте нам:
📞 {center_phone}

Перепрошуємо за незручності.
{center_name}",
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::create($template);
        }
    }
}