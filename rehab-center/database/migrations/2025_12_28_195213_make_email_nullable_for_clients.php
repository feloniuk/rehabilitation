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
        Schema::table('users', function (Blueprint $table) {
            // Drop unique constraint and make email nullable
            $table->dropUnique(['email']);
            $table->string('email')->nullable()->change();
            // Add unique constraint only for non-null emails
            $table->unique('email')->whereNotNull('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('email')->change();
            $table->unique('email');
        });
    }
};
