<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TextBlock;

class TextBlockSeeder extends Seeder
{
    public function run()
    {
        $blocks = [
            // Hero секція
            [
                'key' => 'hero_title',
                'title' => 'Заголовок Hero',
                'content' => 'Професійна реабілітація та відновлення здоров\'я з турботою про кожного пацієнта',
                'type' => 'textarea',
                'order' => 1
            ],
            
            // Чому обирають нас
            [
                'key' => 'features_title',
                'title' => 'Заголовок "Чому обирають нас"',
                'content' => 'Чому обирають нас?',
                'type' => 'text',
                'order' => 10
            ],
            [
                'key' => 'features_subtitle',
                'title' => 'Підзаголовок "Чому обирають нас"',
                'content' => 'Ми поєднуємо сучасні методи лікування з індивідуальним підходом до кожного пацієнта',
                'type' => 'textarea',
                'order' => 11
            ],
            [
                'key' => 'feature_1_title',
                'title' => 'Переваги 1: Заголовок',
                'content' => 'Досвідчені спеціалісти',
                'type' => 'text',
                'order' => 12
            ],
            [
                'key' => 'feature_1_text',
                'title' => 'Переваги 1: Текст',
                'content' => 'Наші майстри мають багаторічний досвід та постійно підвищують кваліфікацію',
                'type' => 'textarea',
                'order' => 13
            ],
            [
                'key' => 'feature_2_title',
                'title' => 'Переваги 2: Заголовок',
                'content' => 'Індивідуальний підхід',
                'type' => 'text',
                'order' => 14
            ],
            [
                'key' => 'feature_2_text',
                'title' => 'Переваги 2: Текст',
                'content' => 'Кожна програма реабілітації розробляється з урахуванням особистих потреб',
                'type' => 'textarea',
                'order' => 15
            ],
            [
                'key' => 'feature_3_title',
                'title' => 'Переваги 3: Заголовок',
                'content' => 'Гарантія результату',
                'type' => 'text',
                'order' => 16
            ],
            [
                'key' => 'feature_3_text',
                'title' => 'Переваги 3: Текст',
                'content' => 'Ми гарантуємо видимі результати та покращення стану здоров\'я',
                'type' => 'textarea',
                'order' => 17
            ],
            
            // Послуги
            [
                'key' => 'services_title',
                'title' => 'Заголовок секції послуг',
                'content' => 'Наші послуги',
                'type' => 'text',
                'order' => 20
            ],
            [
                'key' => 'services_subtitle',
                'title' => 'Підзаголовок секції послуг',
                'content' => 'Широкий спектр реабілітаційних послуг для відновлення та підтримки здоров\'я',
                'type' => 'textarea',
                'order' => 21
            ],
            
            // Майстри
            [
                'key' => 'masters_title',
                'title' => 'Заголовок секції майстрів',
                'content' => 'Наші спеціалісти',
                'type' => 'text',
                'order' => 30
            ],
            [
                'key' => 'masters_subtitle',
                'title' => 'Підзаголовок секції майстрів',
                'content' => 'Команда професіоналів з багаторічним досвідом та постійним розвитком',
                'type' => 'textarea',
                'order' => 31
            ],
            
            // CTA
            [
                'key' => 'cta_title',
                'title' => 'CTA: Заголовок',
                'content' => 'Готові почати шлях до здоров\'я?',
                'type' => 'text',
                'order' => 40
            ],
            [
                'key' => 'cta_subtitle',
                'title' => 'CTA: Підзаголовок',
                'content' => 'Зв\'яжіться з нами прямо зараз та отримайте професійну консультацію',
                'type' => 'textarea',
                'order' => 41
            ],
            
            // Статистика
            [
                'key' => 'stats_specialists_count',
                'title' => 'Статистика: Кількість спеціалістів',
                'content' => '10+',
                'type' => 'text',
                'order' => 50
            ],
            [
                'key' => 'stats_specialists_label',
                'title' => 'Статистика: Підпис спеціалістів',
                'content' => 'Спеціалістів',
                'type' => 'text',
                'order' => 51
            ],
            [
                'key' => 'stats_services_label',
                'title' => 'Статистика: Підпис послуг',
                'content' => 'Видів послуг',
                'type' => 'text',
                'order' => 52
            ],
            [
                'key' => 'stats_clients_count',
                'title' => 'Статистика: Кількість клієнтів',
                'content' => '100+',
                'type' => 'text',
                'order' => 53
            ],
            [
                'key' => 'stats_clients_label',
                'title' => 'Статистика: Підпис клієнтів',
                'content' => 'Задоволених клієнтів',
                'type' => 'text',
                'order' => 54
            ],
            [
                'key' => 'stats_experience_count',
                'title' => 'Статистика: Років досвіду',
                'content' => '5+',
                'type' => 'text',
                'order' => 55
            ],
            [
                'key' => 'stats_experience_label',
                'title' => 'Статистика: Підпис досвіду',
                'content' => 'Років досвіду',
                'type' => 'text',
                'order' => 56
            ],
        ];

        foreach ($blocks as $block) {
            TextBlock::create($block);
        }
    }
}