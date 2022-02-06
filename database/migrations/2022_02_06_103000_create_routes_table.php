<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('blog_id');

            // data
            $table->string('name');
            $table->enum('type', ['single', 'collection']);
            $table->string('match');

            $table->enum('single_type', ['post', 'page'])->nullable();
            $table->enum('collection_type', ['index', 'author', 'tag', 'search'])->nullable();
            $table->string('collection_filter')->nullable();

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
        Schema::dropIfExists('routes');
    }
}
