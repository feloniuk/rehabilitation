<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->integer('duration')->nullable(); // переопределение длительности для конкретного мастера
            $table->timestamps();
            $table->unique(['master_id', 'service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_services');
    }
};
