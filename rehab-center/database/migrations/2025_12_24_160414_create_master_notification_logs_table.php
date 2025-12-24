<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('master_id')->constrained('users')->cascadeOnDelete();
            $table->string('chat_id')->nullable()->comment('Telegram chat_id that was used');
            $table->string('phone')->comment('Master phone (normalized)');
            $table->string('status')->default('pending')->comment('pending, sent, failed');
            $table->string('resolution_source')->nullable()->comment('database, webhook, resolver');
            $table->longText('message')->comment('Notification message text');
            $table->longText('error_message')->nullable()->comment('Error details if failed');
            $table->json('metadata')->nullable()->comment('Additional metadata');
            $table->timestamps();

            $table->index('master_id');
            $table->index('appointment_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_notification_logs');
    }
};
