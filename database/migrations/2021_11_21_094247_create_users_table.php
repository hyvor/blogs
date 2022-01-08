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
            $table->bigInteger('user_id'); // hyvor user ID
            $table->boolean('is_synced', true)->default(false); // synced with hyvor data

            $table->enum('status', ['invited', 'active', 'blocked'])->default('invited');
            $table->enum('role', ['owner', 'admin', 'editor', 'writer', 'contributor', 'finance']);

            // user data
            $table->string('slug');
            $table->string('name', 50);
            $table->string('email');
            $table->string('profile_image')->nullable();
            $table->string('bio')->nullable();
            $table->string('website_url')->nullable();
            $table->string('location', 30)->nullable();

            // social
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_instagram')->nullable();

            // misc
            $table->integer('sort')->default(0); // for ordering in the console
            
            $table->unique(['blog_id', 'slug']);
            $table->unique(['blog_id', 'user_id']);
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
