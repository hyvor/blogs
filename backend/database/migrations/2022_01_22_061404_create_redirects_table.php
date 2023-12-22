<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedirectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();


            // connections
            $table->bigInteger('blog_id')->index();

            $table->string('path');
            $table->string('to');

            $table->enum('type', ['permanent', 'temporary']);

            $table->unique(['blog_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redirects');
    }
}
