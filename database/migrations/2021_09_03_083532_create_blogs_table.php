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

            // connections
            $table->bigInteger('user_id'); // hyvor user id
            $table->bigInteger('theme_id')->nullable();


            // data
            $table->string('subdomain')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();

            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_instagram')->nullable();

            // $table->string('name')->unique();
            $table->string('website_url');
            $table->string('title');
            $table->string('short_description');
            $table->integer('author_id');

            // Soft Delete
            $table->string('deleted_at')->nullable();


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
