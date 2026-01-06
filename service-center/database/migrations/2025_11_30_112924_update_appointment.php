<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Если колонки нет, создаем ее
        if (!Schema::hasColumn('appointments', 'telegram_notification_sent')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->boolean('telegram_notification_sent')
                      ->default(false)
                      ->after('notes'); // Укажите после какого поля добавить
            });
        }
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('telegram_notification_sent');
        });
    }
};