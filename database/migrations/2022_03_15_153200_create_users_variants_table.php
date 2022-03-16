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
        Schema::create('users_variants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connection
            $table->bigInteger('blog_id');
            $table->bigInteger('language_id')->index();
            $table->bigInteger('user_id')->nullable(); // hyvor user ID
            $table->boolean('is_synced', true)->default(false); // synced with hyvor data

            $table->enum('status', ['invited', 'active', 'blocked'])->default('invited');
            $table->enum('role', ['owner', 'admin', 'editor', 'writer', 'contributor', 'finance']);

            // user data
            $table->string('slug');
            $table->string('name', 50);
            $table->string('email');
            $table->string('bio')->nullable();
            $table->string('url')->nullable();
            $table->string('location', 30)->nullable();


            // misc
            $table->integer('sort')->default(0); // for ordering in the console

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
