<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('blog_id')->index();

            $table->string('slug');

            $table->integer('posts_count')->default(0);

            $table->text('code_head')->nullable();
            $table->text('code_foot')->nullable();

            $table->unique(['blog_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
