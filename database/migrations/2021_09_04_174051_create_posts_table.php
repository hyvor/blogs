<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // time
            $table->timestamps();
            $table->timestamp('published_at')->nullable();

            // main
            $table->bigInteger('blog_id');
            $table->string('slug');
            $table->string('title');
            $table->string('description', 350);
            $table->text('content');

            // status
            $table->enum('status', ['published', 'draft', 'scheduled', 'deleted']);
            $table->boolean('is_featured')->default(false);

            // seo
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical_url')->nullable();

            // feature and social media
            $table->bigInteger('feature_image_media_id')->nullable();
            $table->bigInteger('og_image_media_id')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->bigInteger('twitter_image_media_id')->nullable();
            $table->string('twitter_title')->nullable();
            $table->string('twitter_description')->nullable();

            // code
            $table->text('code_head')->nullable();
            $table->text('code_after_content')->nullable();
            $table->text('code_footer')->nullable();

            // other
            $table->tinyInteger('reading_time');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
