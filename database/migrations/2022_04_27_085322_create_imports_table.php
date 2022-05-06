<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

             // connections
            $table->bigInteger('blog_id')->index();

            // data
            $table->string('name')->nullable();
            $table->integer('size')->default(0);
            $table->enum('type', ['wordpress', 'ghost', 'hyvor', 'blogger', 'tumblr', 'substack'])->nullable();
            $table->enum('status', ['pending', 'success', 'error'])->nullable();
             
            $table->json('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imports');
    }
};
