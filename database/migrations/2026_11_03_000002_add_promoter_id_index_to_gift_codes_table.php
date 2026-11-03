<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Production recorded the promoters migration before its index existed.
    public function up(): void
    {
        if (!Schema::hasIndex('gift_codes', ['promoter_id'])) {
            Schema::table('gift_codes', function (Blueprint $table) {
                $table->index('promoter_id');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
