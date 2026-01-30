<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\MasterService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentValidationTest extends TestCase
{
    use RefreshDatabase;

    private $master;

    private $service;

    private $masterService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test master with work schedule
        $this->master = User::create([
            'name' => 'Test Master',
            'email' => 'master@test.com',
            'phone' => '+380661234567',
            'password' => bcrypt('password'),
            'role' => 'master',
            'work_schedule' => [
                'monday' => ['is_working' => 1, 'start' => '11:00', 'end' => '16:00'],
                'tuesday' => ['is_working' => 1, 'start' => '11:00', 'end' => '16:00'],
                'wednesday' => ['is_working' => 1, 'start' => '11:00', 'end' => '16:00'],
                'thursday' => ['is_working' => 1, 'start' => '11:00', 'end' => '16:00'],
                'friday' => ['is_working' => 1, 'start' => '11:00', 'end' => '16:00'],
                'saturday' => ['is_working' => 1, 'start' => '11:00', 'end' => '15:00'],
                'sunday' => ['start' => '10:00', 'end' => '15:00'],
            ],
        ]);

        // Create test service
        $this->service = Service::create([
            'name' => 'Test Service',
            'description' => 'Test Description',
            'duration' => 40,
            'is_active' => true,
        ]);

        // Create master-service relation
        $this->masterService = MasterService::create([
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'price' => 500,
            'duration' => null, // Will use service duration
        ]);
    }

    public function test_valid_time_slot_is_accepted()
    {
        // Thursday 2026-01-29
        $response = $this->post(route('appointment.store'), [
            'name' => 'John Doe',
            'phone' => '+380661234567',
            'email' => 'john@example.com',
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '11:00', // Valid time (first slot)
        ]);

        $response->assertRedirect(route('appointment.success'));
        $this->assertDatabaseHas('appointments', [
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '11:00',
        ]);
    }

    public function test_invalid_custom_time_is_rejected()
    {
        // Try to book at 14:20 (custom time, not in 30-minute slots)
        $response = $this->post(route('appointment.store'), [
            'name' => 'John Doe',
            'phone' => '+380661234567',
            'email' => 'john@example.com',
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '14:20', // Invalid: not in 30-minute intervals
        ]);

        $response->assertSessionHasErrors('appointment_time');
        $this->assertDatabaseMissing('appointments', [
            'appointment_date' => '2026-01-29',
            'appointment_time' => '14:20',
        ]);
    }

    public function test_conflicting_appointment_blocks_overlapping_slots()
    {
        // Create an existing appointment: 13:00-13:40
        Appointment::create([
            'client_id' => User::factory()->create(['role' => 'client'])->id,
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '13:00',
            'duration' => 40,
            'price' => 500,
            'status' => 'scheduled',
        ]);

        // Try to book at 13:00 (direct conflict)
        $response1 = $this->post(route('appointment.store'), [
            'name' => 'John Doe',
            'phone' => '+380661234567',
            'email' => 'john@example.com',
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '13:00',
        ]);
        $response1->assertSessionHasErrors('appointment_time');

        // Try to book at 13:30 (overlap with 13:00-13:40)
        $response2 = $this->post(route('appointment.store'), [
            'name' => 'Jane Doe',
            'phone' => '+380669876543',
            'email' => 'jane@example.com',
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '13:30',
        ]);
        $response2->assertSessionHasErrors('appointment_time');

        // Book at 14:00 (should succeed - no conflict)
        $response3 = $this->post(route('appointment.store'), [
            'name' => 'Bob Smith',
            'phone' => '+380675551234',
            'email' => 'bob@example.com',
            'master_id' => $this->master->id,
            'service_id' => $this->service->id,
            'appointment_date' => '2026-01-29',
            'appointment_time' => '14:00',
        ]);
        $response3->assertRedirect(route('appointment.success'));
        $this->assertDatabaseHas('appointments', [
            'appointment_time' => '14:00',
        ]);
    }

    public function test_30_minute_slots_are_generated_correctly()
    {
        // Verify that slots are generated with 30-minute intervals
        // by checking available slots through the controller
        $controller = new \App\Http\Controllers\MasterController();
        $response = $controller->getAvailableSlots(
            $this->master->id,
            '2026-01-29',
            $this->service->id
        );

        $slots = json_decode($response->getContent(), true);

        // Expected slots: 11:00, 11:30, 12:00, 12:30, 13:00, 13:30, 14:00, 14:30, 15:00
        $this->assertContains('11:00', $slots);
        $this->assertContains('11:30', $slots);
        $this->assertContains('12:00', $slots);
        $this->assertContains('14:30', $slots);
        $this->assertContains('15:00', $slots);

        // Should NOT contain arbitrary times
        $this->assertNotContains('11:20', $slots);
        $this->assertNotContains('14:20', $slots);
        $this->assertNotContains('14:45', $slots);
    }
}
