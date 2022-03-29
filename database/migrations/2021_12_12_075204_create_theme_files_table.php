<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemeFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theme_files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Connections
            $table->bigInteger('themable_id');
            $table->string('themable_type'); // blog|local_dev

            // data
            $table->enum('folder', ['templates', 'assets', 'styles', 'lang'])->nullable();
            $table->string('name');
            $table->binary('content')->nullable();

            $table->unique(['themable_id', 'themable_type', 'name', 'folder']);
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
