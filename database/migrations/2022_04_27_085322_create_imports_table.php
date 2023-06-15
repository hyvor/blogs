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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('blog_id')->index();

            // data
            $table->string('name');
            $table->enum('type', ['sitemap', 'wordpress']);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');

            $table->integer('posts_count')->default(0);
            $table->integer('pages_count')->default(0);
            $table->integer('tags_count')->default(0);
            $table->integer('users_count')->default(0);
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
