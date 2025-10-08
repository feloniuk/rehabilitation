<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Адміністратор',
            'email' => 'admin@rehab.center',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create services
        $services = [
            ['name' => 'Масаж', 'description' => 'Лікувальний масаж', 'duration' => 60],
            ['name' => 'ЛФК', 'description' => 'Лікувальна фізкультура', 'duration' => 45],
            ['name' => 'Фізіотерапія', 'description' => 'Фізіотерапевтичні процедури', 'duration' => 30],
            ['name' => 'Мануальна терапія', 'description' => 'Мануальна терапія хребта', 'duration' => 40],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        // Create pages
        $pages = [
            [
                'slug' => 'about',
                'title' => 'Про нас',
                'content' => '<h2>Про наш центр</h2><p>Ми надаємо якісні послуги реабілітації...</p>',
                'is_active' => true,
            ],
            [
                'slug' => 'contacts',
                'title' => 'Контакти',
                'content' => '<h2>Наші контакти</h2><p>Телефон: +380...</p>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }

        // Create settings
        $settings = [
            'center_name' => 'Реабілітаційний центр "Здоров\'я"',
            'center_address' => 'м. Київ, вул. Хрещатик, 1',
            'center_coordinates' => '50.4501,30.5234',
            'center_phone' => '+38 (044) 123-45-67',
            'center_email' => 'info@rehab.center',
            'working_hours' => 'Пн-Пт: 9:00-18:00, Сб: 10:00-15:00',
        ];

        foreach ($settings as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }
        
        $this->call([
            TextBlockSeeder::class,
        ]);
    }
}