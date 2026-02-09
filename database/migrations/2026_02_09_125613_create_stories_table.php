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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('narrator')->nullable();
            $table->string('duration_label')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_dummy')->default(false);
            $table->boolean('locked')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->string('audio_url')->nullable();
            $table->string('audio_path')->nullable();
            $table->json('effects')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'subcategory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
