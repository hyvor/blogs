<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // time
            $table->timestamps();
            $table->softDeletes();

            // connections
            $table->bigInteger('blog_id')->index();

            // status
            $table->boolean('is_page')->default(false);
            $table->boolean('is_featured')->default(false);

            // basic
            $table->string('slug')->nullable();

            // advanced
            $table->string('canonical_url')->nullable();
            $table->text('code_head')->nullable();
            $table->text('code_foot')->nullable();

            // other
            $table->tinyInteger('reading_time')->nullable();

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
        Schema::dropIfExists('posts');
    }
}
