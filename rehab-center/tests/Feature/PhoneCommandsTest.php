<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneCommandsTest extends TestCase
{
    use RefreshDatabase;

    // --- phones:normalize ---

    public function test_normalize_updates_non_normalized_phone(): void
    {
        $user = User::factory()->create(['phone' => '0661234567', 'role' => 'client']);
        // Bypass mutator to store raw value
        User::query()->where('id', $user->id)->update(['phone' => '0661234567']);

        $this->artisan('phones:normalize')->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '+380661234567']);
    }

    public function test_normalize_skips_collision_and_reports(): void
    {
        User::factory()->create(['phone' => '+380661234567', 'role' => 'client']);
        $duplicate = User::factory()->create(['phone' => '+380661234567', 'role' => 'client']);
        User::query()->where('id', $duplicate->id)->update(['phone' => '0661234567']);

        $this->artisan('phones:normalize')
            ->assertSuccessful()
            ->expectsOutputToContain('Колізій');

        // Duplicate's phone must NOT be changed
        $this->assertDatabaseHas('users', ['id' => $duplicate->id, 'phone' => '0661234567']);
    }

    public function test_normalize_does_not_delete_or_merge_users(): void
    {
        $userA = User::factory()->create(['phone' => '+380661234567', 'role' => 'client']);
        $userB = User::factory()->create(['phone' => '+380661234567', 'role' => 'client']);
        User::query()->where('id', $userB->id)->update(['phone' => '0661234567']);

        $countBefore = User::withTrashed()->count();

        $this->artisan('phones:normalize')->assertSuccessful();

        $this->assertEquals($countBefore, User::withTrashed()->count());
        $this->assertNull(User::withTrashed()->find($userA->id)->deleted_at);
        $this->assertNull(User::withTrashed()->find($userB->id)->deleted_at);
    }

    public function test_normalize_dry_run_makes_no_changes(): void
    {
        $user = User::factory()->create(['phone' => '0661234567', 'role' => 'client']);
        User::query()->where('id', $user->id)->update(['phone' => '0661234567']);

        $this->artisan('phones:normalize', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '0661234567']);
    }

    // --- phones:merge ---

    public function test_merge_moves_appointments_via_model(): void
    {
        $primary = User::factory()->create(['role' => 'client']);
        $duplicate = User::factory()->create(['role' => 'client']);
        $master = User::factory()->create(['role' => 'master']);
        $service = \App\Models\Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'client_id' => $duplicate->id,
            'master_id' => $master->id,
            'service_id' => $service->id,
        ]);

        $this->artisan('phones:merge', [
            'primary' => $primary->id,
            'duplicate' => $duplicate->id,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'client_id' => $primary->id,
        ]);
    }

    public function test_merge_soft_deletes_duplicate_not_force_deletes(): void
    {
        $primary = User::factory()->create(['role' => 'client']);
        $duplicate = User::factory()->create(['role' => 'client']);

        $this->artisan('phones:merge', [
            'primary' => $primary->id,
            'duplicate' => $duplicate->id,
            '--force' => true,
        ])->assertSuccessful();

        // Must still exist in DB with deleted_at set
        $this->assertSoftDeleted('users', ['id' => $duplicate->id]);
        $this->assertDatabaseHas('users', ['id' => $primary->id, 'deleted_at' => null]);
    }

    public function test_merge_creates_audit_log_for_each_appointment(): void
    {
        $primary = User::factory()->create(['role' => 'client']);
        $duplicate = User::factory()->create(['role' => 'client']);
        $master = User::factory()->create(['role' => 'master']);
        $service = \App\Models\Service::factory()->create();

        Appointment::factory()->count(2)->create([
            'client_id' => $duplicate->id,
            'master_id' => $master->id,
            'service_id' => $service->id,
        ]);

        $this->artisan('phones:merge', [
            'primary' => $primary->id,
            'duplicate' => $duplicate->id,
            '--force' => true,
        ])->assertSuccessful();

        // Observer має записати updated для кожної appointment
        $this->assertEquals(2, \App\Models\AppointmentAuditLog::where('action', 'updated')->count());
        $this->assertDatabaseHas('appointment_audit_logs', ['action' => 'updated']);
    }

    public function test_merge_fails_with_same_ids(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->artisan('phones:merge', [
            'primary' => $user->id,
            'duplicate' => $user->id,
        ])->assertFailed();
    }

    public function test_merge_fails_with_nonexistent_user(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->artisan('phones:merge', [
            'primary' => $user->id,
            'duplicate' => 999999,
        ])->assertFailed();
    }
}
