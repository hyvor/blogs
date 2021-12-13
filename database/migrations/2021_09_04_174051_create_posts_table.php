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
            $table->timestamp('published_at')->nullable();

            // connections
            $table->bigInteger('blog_id');

            // data
            $table->text('content');
            $table->string('slug');
            $table->string('title');
            $table->string('description', 350);
            $table->string('canonical_url')->nullable();
            $table->string('featured_image')->nullable();

            // status
            $table->enum('status', ['published', 'draft', 'scheduled', 'deleted']);
            $table->boolean('is_featured')->default(false);

            // code
            $table->text('code_head')->nullable();
            $table->text('code_after_content')->nullable();
            $table->text('code_footer')->nullable();

            // other
            $table->tinyInteger('reading_time');
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
