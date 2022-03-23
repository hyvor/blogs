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
        Schema::create('tags_variants', function (Blueprint $table) {
            $table->id();

            // connections
            $table->bigInteger('tag_id')->index();
            $table->bigInteger('language_id')->index();

            // data
            $table->string('name');
            $table->string('description')->nullable(); 

            $table->timestamps();

            // $table->string('updated_a')->index();
            // $table->string('created_at')->index();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags_variants');
    }
};
