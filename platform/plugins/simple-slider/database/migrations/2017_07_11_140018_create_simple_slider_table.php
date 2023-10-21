<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('simple_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('key', 120);
            $table->string('description', 255)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('simple_slider_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simple_slider_id');
            $table->string('title', 255)->nullable();
            $table->string('image', 255);
            $table->string('link', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simple_sliders');
        Schema::dropIfExists('simple_slider_items');
    }
};
