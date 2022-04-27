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

            $table->string('name');

            $table->enum('platform', ['wordpress', 'ghost', 'hyvor', 'blogger', 'tumblr', 'substack'])->nullable();

            $table->integer('size')->default(0); // in bytes
            $table->string('extension')->index(); // file extension
            $table->string('original_name'); // original filename (in user's browser)

            $table->integer('posts_count')->default(0);
            $table->integer('author_count')->default(0);
            $table->integer('tag_count')->default(0);
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
