<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            // connection
            $table->bigInteger('blog_id');

            // data
            $table->enum('folder', ['templates', 'assets', 'styles', 'lang'])->nullable();
            $table->string('name');
            // $table->blob('content')->nullable();

            // index
            $table->unique(['blog_id', 'folder', 'name']);
        });

        // https://stackoverflow.com/a/20099781/9059939
        // upto 16MB
        DB::statement('ALTER TABLE theme_files ADD content bytea NULL');
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
