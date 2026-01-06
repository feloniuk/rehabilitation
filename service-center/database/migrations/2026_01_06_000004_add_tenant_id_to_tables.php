<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need tenant_id column.
     */
    protected array $tables = [
        'services',
        'appointments',
        'pages',
        'notification_templates',
        'settings',
        'text_blocks',
        'service_faqs',
        'notification_logs',
        'master_notification_logs',
        'master_services',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('tenant_id')
                        ->nullable()
                        ->after('id')
                        ->constrained()
                        ->nullOnDelete();
                    $blueprint->index('tenant_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->dropForeign([$table . '_tenant_id_foreign']);
                    $blueprint->dropIndex([$table . '_tenant_id_index']);
                    $blueprint->dropColumn('tenant_id');
                });
            }
        }
    }
};
