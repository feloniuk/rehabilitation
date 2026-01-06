<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // hero_title, hero_subtitle, features_title тощо
            $table->string('title'); // Назва для адміна
            $table->text('content'); // Контент
            $table->string('type')->default('text'); // text, textarea, html
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('text_blocks');
    }
};