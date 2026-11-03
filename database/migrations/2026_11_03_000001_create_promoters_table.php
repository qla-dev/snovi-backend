<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoters', function (Blueprint $table) {
            $table->id();
            // snovi.fm/promoters/{username}
            $table->string('username', 60)->unique();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('headline', 160)->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('instagram', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('gift_codes', function (Blueprint $table) {
            $table->foreignId('promoter_id')->nullable()->after('plan')->constrained('promoters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gift_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promoter_id');
        });

        Schema::dropIfExists('promoters');
    }
};
