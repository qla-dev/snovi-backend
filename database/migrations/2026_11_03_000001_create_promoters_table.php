<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Safe to re-run: the first production run created the table and column, then failed on the
    // foreign key (old gift_codes rows hold 0000-00-00 dates that strict MySQL rejects on rebuild).
    public function up(): void
    {
        if (!Schema::hasTable('promoters')) {
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
        }

        if (!Schema::hasColumn('gift_codes', 'promoter_id')) {
            Schema::table('gift_codes', function (Blueprint $table) {
                $table->unsignedBigInteger('promoter_id')->nullable()->after('plan');
            });
        }

        // No foreign key (see above); Promoter clears promoter_id itself when it is deleted.
        if (!Schema::hasIndex('gift_codes', ['promoter_id'])) {
            Schema::table('gift_codes', function (Blueprint $table) {
                $table->index('promoter_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('gift_codes', function (Blueprint $table) {
            $table->dropIndex(['promoter_id']);
            $table->dropColumn('promoter_id');
        });

        Schema::dropIfExists('promoters');
    }
};
