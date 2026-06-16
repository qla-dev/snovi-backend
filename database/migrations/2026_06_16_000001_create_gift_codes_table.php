<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 190)->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('used_date')->nullable();

            $table->index('used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_codes');
    }
};
