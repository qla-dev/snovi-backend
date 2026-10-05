<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_codes', function (Blueprint $table) {
            // null = made in the admin; "web" = issued for a RevenueCat Billing purchase on snovi.fm.
            $table->string('source', 20)->nullable()->after('email');
            // yearly | monthly (web codes only).
            $table->string('plan', 20)->nullable()->after('source');
            $table->string('rc_app_user_id', 191)->nullable()->index()->after('plan');
            $table->string('rc_product_id', 100)->nullable()->after('rc_app_user_id');
            $table->timestamp('voucher_sent_at')->nullable()->after('rc_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('gift_codes', function (Blueprint $table) {
            $table->dropIndex(['rc_app_user_id']);
            $table->dropColumn(['source', 'plan', 'rc_app_user_id', 'rc_product_id', 'voucher_sent_at']);
        });
    }
};
