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
            $table->bigInteger('icon_media_id');
            $table->bigInteger('logo_media_id');
            

            $table->string('name')->unique();
            $table->string('website_url');
            $table->string('title');
            $table->string('short_description');
            $table->integer('author_id');
            $table->integer('theme_id');
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
