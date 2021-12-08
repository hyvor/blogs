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
            $table->unsignedBigInteger('theme_id')->unsigned();
            $table->string('name');
            $table->text('content');
            $table->timestamps();

            $table->foreign('theme_id')->references('id')->on('themes');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_files');
    }
}
