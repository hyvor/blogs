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
            $table->bigInteger('hyvor_user_id'); // hyvor user id (owner)
            $table->bigInteger('theme_id')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('featured_image_Url')->nullable();

            // data
            $table->string('subdomain')->unique();
            $table->enum('type', ['default', 'temp'])->default('default');
            $table->bigInteger('dev_theme_id')->nullable();

            $table->enum('hosting_at', ['subdomain', 'domain', 'self'])->default('subdomain');
            $table->string('hosting_domain')->nullable()->unique(); // for domain
            $table->string('hosting_url')->nullable(); // for self

            $table->json('meta')->nullable();

            $table->string('api_key_data')->nullable();
            $table->string('api_key_console')->nullable();

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
