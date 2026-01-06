<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MasterNotificationLog;
use App\Models\MasterService;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Page;
use App\Models\Service;
use App\Models\ServiceFaq;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TextBlock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateSingleTenantToMultiTenantSeeder extends Seeder
{
    /**
     * Migrate existing single-tenant data to multi-tenant structure.
     */
    public function run(): void
    {
        $this->command->info('Starting migration to multi-tenant...');

        DB::transaction(function () {
            // 1. Find or create first admin as tenant owner
            $admin = User::where('role', 'admin')->first();

            if (!$admin) {
                $this->command->warn('No admin user found. Creating default admin...');
                $admin = User::create([
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'admin',
                    'is_active' => true,
                ]);
            }

            // 2. Set admin as super admin
            $admin->update(['is_super_admin' => true]);
            $this->command->info("Set {$admin->name} as super admin");

            // 3. Get tenant name from settings
            $tenantName = Setting::withoutGlobalScope('tenant')
                ->where('key', 'center_name')
                ->first()?->value ?? 'Реабілітаційний центр';

            // 4. Create tenant
            $slug = Str::slug($tenantName);

            // Ensure unique slug
            $counter = 1;
            $baseSlug = $slug;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $tenant = Tenant::create([
                'name' => $tenantName,
                'slug' => $slug,
                'owner_id' => $admin->id,
                'is_active' => true,
                'trial_ends_at' => null, // No trial for existing tenant
                'settings' => [
                    'center_name' => $tenantName,
                    'center_phone' => Setting::withoutGlobalScope('tenant')->where('key', 'center_phone')->first()?->value,
                    'center_address' => Setting::withoutGlobalScope('tenant')->where('key', 'center_address')->first()?->value,
                ],
            ]);

            $this->command->info("Created tenant: {$tenant->name} (slug: {$tenant->slug})");

            // 5. Assign all users to tenant with appropriate roles
            $users = User::all();
            foreach ($users as $user) {
                $role = $this->mapLegacyRole($user->role);

                // Owner for admin users
                if ($user->id === $admin->id) {
                    $role = 'owner';
                }

                $user->tenants()->attach($tenant->id, ['role' => $role]);
                $this->command->info("  Assigned {$user->name} as {$role}");
            }

            // 6. Update tenant_id on all existing records
            $tables = [
                'services' => Service::class,
                'appointments' => Appointment::class,
                'pages' => Page::class,
                'settings' => Setting::class,
                'text_blocks' => TextBlock::class,
                'notification_templates' => NotificationTemplate::class,
                'notification_logs' => NotificationLog::class,
                'master_notification_logs' => MasterNotificationLog::class,
                'master_services' => MasterService::class,
                'service_faqs' => ServiceFaq::class,
            ];

            foreach ($tables as $tableName => $modelClass) {
                $count = $modelClass::withoutGlobalScope('tenant')
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $tenant->id]);

                $this->command->info("  Updated {$count} records in {$tableName}");
            }

            $this->command->info('');
            $this->command->info('Migration completed successfully!');
            $this->command->info("Tenant URL: /{$tenant->slug}");
            $this->command->info("Admin URL: /{$tenant->slug}/admin");
        });
    }

    /**
     * Map legacy role to new tenant role.
     */
    private function mapLegacyRole(string $legacyRole): string
    {
        return match ($legacyRole) {
            'admin' => 'admin',
            'master' => 'master',
            'client' => 'client',
            default => 'client',
        };
    }
}
