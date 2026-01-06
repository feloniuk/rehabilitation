<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Конвертируем все строковые значения duration в integer
        DB::table('appointments')->whereNotNull('duration')->update([
            'duration' => DB::raw('CAST(duration AS UNSIGNED)')
        ]);
        
        DB::table('services')->whereNotNull('duration')->update([
            'duration' => DB::raw('CAST(duration AS UNSIGNED)')
        ]);
        
        DB::table('master_services')->whereNotNull('duration')->update([
            'duration' => DB::raw('CAST(duration AS UNSIGNED)')
        ]);
    }

    public function down()
    {
        // Rollback не нужен
    }
};