<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// URL data for embeds
class CreateUrlDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_data', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            /**
             * If first fetched for link, and then checked for rich
             * We may assume that link only has "link" type,
             * even though "rich" is available.
             * 
             * So, 
             */
            $table->enum('fetch_type', ['link', 'rich']);

            $table->string('url');
            $table->string('final_url');
            $table->enum('type', ['link', 'rich', 'error']);
            $table->text('html')->nullable(); // rich html, if available
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('icon')->nullable();
            $table->string('site')->nullable();
            
            $table->unique(['fetch_type', 'url']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('embeds');
    }
}
