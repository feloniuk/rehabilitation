<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory()->create(['role' => 'client'])->id,
            'master_id' => User::factory()->create(['role' => 'master'])->id,
            'service_id' => \App\Models\Service::factory()->create()->id,
            'appointment_date' => now()->addDays(rand(1, 30))->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'duration' => 60,
            'price' => 500.00,
            'status' => 'scheduled',
        ];
    }
}
