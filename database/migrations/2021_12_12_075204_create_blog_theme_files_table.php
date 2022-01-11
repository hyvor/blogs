<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogThemeFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_theme_files', function (Blueprint $table) {
            $table->id();

            // Connections
            $table->bigInteger('blog_id')->nullable();
            
            $table->string('name')->nullable();
            $table->binary('content')->nullable();
            $table->enum('type', ['templates', 'assets','styles'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_theme_files');
    }
}
