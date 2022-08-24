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
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('webhook_id');

            // data
            $table->enum('status', ['pending', 'retrying', 'failed', 'success']);

            $table->string('url');
            $table->string('event');
            $table->json('data');

            $table->string('response', 1024)->nullable();
            $table->integer('http_status')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
