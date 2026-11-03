<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promoters', function (Blueprint $table) {
            // Encrypted: the admin reads it back to hand it to the promoter.
            $table->text('pin')->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('promoters', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
