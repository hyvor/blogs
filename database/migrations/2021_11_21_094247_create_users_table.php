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
            $table->bigInteger('picture_id')->nullable();

            $table->boolean('is_synced', true)->default(false); // synced with hyvor data

            $table->enum('status', ['invited', 'active', 'blocked'])->default('invited');
            $table->enum('role', ['owner', 'admin', 'editor', 'writer', 'contributor', 'finance']);

            // user data
            $table->string('slug');
            $table->string('email');
            $table->string('url')->nullable();

            // social
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_instagram')->nullable();

            // misc
            $table->integer('sort')->default(0); // for ordering in the console

            $table->unique(['blog_id', 'slug']);
            $table->unique(['blog_id', 'hyvor_user_id']);
            $table->unique(['blog_id', 'picture_id']);
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
