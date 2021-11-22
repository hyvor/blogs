<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connection
            $table->bigInteger('user_id'); // hyvor user id

            // data
            $table->enum('status', ['invited', 'active', 'blocked']);
            $table->enum('type', ['owner', 'admin', 'editor', 'author', 'contributor']);
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
