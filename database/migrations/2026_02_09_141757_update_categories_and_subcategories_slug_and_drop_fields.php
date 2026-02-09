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
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'code')) {
                $table->renameColumn('code', 'slug');
            }
            if (Schema::hasColumn('categories', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::table('subcategories', function (Blueprint $table) {
            if (Schema::hasColumn('subcategories', 'code')) {
                $table->renameColumn('code', 'slug');
            }
            if (Schema::hasColumn('subcategories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            if (Schema::hasColumn('subcategories', 'slug')) {
                $table->renameColumn('slug', 'code');
            }
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'slug')) {
                $table->renameColumn('slug', 'code');
            }
            $table->string('type')->default('content');
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
    }
};
