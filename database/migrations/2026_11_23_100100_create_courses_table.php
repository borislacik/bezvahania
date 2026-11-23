<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Workshop types. Text columns are JSON so they can be translated (spatie/laravel-translatable).
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('badge')->nullable();
            $table->json('short_description');
            $table->json('description')->nullable();
            $table->json('duration_label');
            $table->json('equipment')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
