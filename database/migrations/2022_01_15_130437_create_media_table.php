<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('blog_id')->index();

            // data
            $table->integer('post_id')->nullable(); // post this media is related to (uploaded from)
            $table->string('name')->nullable();  // unique name of the uploaded file
            $table->integer('size')->default(0); // in bytes
            $table->string('original_name'); // original filename (in user's browser)
            $table->string('extension')->nullable()->index(); // file extension

            $table->unique(['blog_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
