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
        Schema::create('user_variants', function (Blueprint $table) {
            $table->id();
            // $table->timestamps();

            // connection
            $table->bigInteger('user_id')->index(); // The id from the users table.
            $table->bigInteger('language_id')->index();

            // user data
            $table->string('name', 50)->nullable()->index();
            $table->string('bio')->nullable();
            $table->string('location', 50)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_variants');
    }
};
