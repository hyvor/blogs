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

            // data
            $table->string('subdomain')->unique();
            $table->string('name');
            $table->string('description');
            $table->string('icon');

            $table->string('social_facebook');
            $table->string('social_twitter');
            $table->string('social_linkedin');
            $table->string('social_youtube');
            $table->string('social_instagram');

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
