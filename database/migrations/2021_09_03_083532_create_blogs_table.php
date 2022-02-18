<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            // connections
            $table->bigInteger('user_id'); // hyvor user id
            $table->bigInteger('theme_id')->nullable();

            // data
            $table->string('subdomain')->unique();
            $table->enum('type', ['normal', 'dev', 'temp'])->default('normal');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('featured_image')->nullable();

            $table->enum('hosting_at', ['subdomain', 'domain', 'self'])->default('subdomain');
            $table->string('hosting_domain')->nullable()->unique(); // for domain
            $table->string('hosting_url')->nullable(); // for self

            $table->boolean('seo_indexing')->default(true);
            $table->text('seo_robots')->nullable();

            $table->text('code_header')->nullable();
            $table->text('code_footer')->nullable();

            $table->text('newsletter_code')->nullable();
            $table->text('comments_code')->nullable();
            $table->enum('comments_type', ['ht', 'other'])->default('ht'); // hyvor talk
            $table->bigInteger('comments_ht_website_id')->nullable();
            $table->string('comments_ht_api_key')->nullable();
        
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_github')->nullable();

            $table->string('edited_at')->nullable();

            $table->integer('posts_count')->default(0);
            $table->integer('users_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}
