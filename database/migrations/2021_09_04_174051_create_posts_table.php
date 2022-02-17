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
            $table->softDeletes();

            // connections
            $table->bigInteger('blog_id')->index();
            $table->bigInteger('language_id')->index();

            // status
            $table->enum('status', ['published', 'draft', 'scheduled', 'deleted'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_page')->default(false);

            // basic
            $table->text('content')->nullable();
            $table->text('content_unsaved')->nullable();
            $table->string('slug')->nullable();
            $table->string('title')->nullable();
            $table->string('description', 350)->default('');
            $table->string('featured_image')->nullable();

            // advanced
            $table->string('canonical_url')->nullable();
            $table->text('code_head')->nullable();
            $table->text('code_foot')->nullable();

            // other
            $table->tinyInteger('reading_time')->nullable();

            $table->unique(['blog_id', 'language_id', 'slug']);
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
