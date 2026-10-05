<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->nullable();
            $table->text('caption');
            // Public paths (under public/) of the rendered 4:5 slides, in carousel order.
            $table->json('slides');
            $table->json('sources')->nullable();
            // UTC; social:publish-due posts it once this has passed.
            $table->timestamp('scheduled_at')->index();
            // scheduled | publishing | published | partial | failed | cancelled
            $table->string('status', 20)->default('scheduled')->index();
            $table->boolean('share_facebook')->default(true);
            $table->boolean('share_instagram')->default(true);
            $table->string('fb_post_id', 100)->nullable();
            $table->string('fb_error', 500)->nullable();
            $table->string('ig_media_id', 100)->nullable();
            $table->string('ig_error', 500)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
