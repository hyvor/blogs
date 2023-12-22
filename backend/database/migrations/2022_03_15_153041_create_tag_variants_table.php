<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_variants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('tag_id');
            $table->bigInteger('language_id');

            // data
            $table->string('name')->nullable();
            $table->string('description')->nullable();

            // indexes
            $table->unique(['tag_id', 'language_id']);
            $table->index('tag_id');
            $table->index('language_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_variants');
    }
};
