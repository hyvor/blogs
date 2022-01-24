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
            $table->timestamps();

            // Connections
            $table->bigInteger('blog_id')->nullable();

            // data
            $table->string('name')->nullable();
            $table->binary('content')->nullable();
            $table->enum('folder', ['templates', 'assets', 'styles', 'lang'])->nullable();

            $table->unique(['blog_id', 'name', 'folder']);
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
