<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_codes', function (Blueprint $table) {
            $table->string('email')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('gift_codes', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
