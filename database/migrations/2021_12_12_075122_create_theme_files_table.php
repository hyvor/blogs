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

            // Connections
            $table->bigInteger('theme_id')->nullable();

            $table->string('name');
            $table->binary('content');
            $table->enum('type', ['template', 'asset']);

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
        Schema::dropIfExists('theme_files');
    }
}
