<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connection
            $table->bigInteger('blog_id');
            $table->bigInteger('user_id'); // hyvor user id

            // data
            $table->enum('status', ['invited', 'active', 'blocked']);
            $table->enum('role', ['owner', 'admin', 'editor', 'writer', 'contributor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_members');
    }
}
