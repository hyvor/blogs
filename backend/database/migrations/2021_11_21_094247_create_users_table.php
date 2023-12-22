<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connection
            $table->bigInteger('blog_id');
            $table->bigInteger('hyvor_user_id')->nullable(); // hyvor user ID

            $table->enum('role', ['owner', 'admin', 'editor', 'writer', 'contributor', 'finance']);
            $table->enum('status', ['invited', 'active', 'blocked'])->default('invited');

            // user data
            $table->string('slug');
            $table->string('email')->nullable();
            $table->string('website_url')->nullable();
            $table->string('picture_url')->nullable();

            // social
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_github')->nullable();

            // misc
            $table->integer('posts_count')->default(0);
            $table->integer('sort')->default(0); // for ordering in the console

            // indexes
            $table->unique(['blog_id', 'slug']);
            $table->unique(['blog_id', 'hyvor_user_id']);
            $table->index(['blog_id', 'created_at']);
            $table->index(['blog_id', 'posts_count']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_members');
    }
}
