<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// URL data for embeds
class CreateEmbedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('embeds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('url')->unique();
            $table->enum('type', ['link', 'rich', 'error']);
            $table->text('html')->nullable(); // rich html, if available
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('thumbnail')->nullable();
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
