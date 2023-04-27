<?php declare(strict_types=1);

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
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('published_at')->nullable();

            // connections
            $table->bigInteger('blog_id');

            // status
            $table->boolean('is_page')->default(false);
            $table->boolean('is_featured')->default(false);

            // data
            // $table->string('slug')->nullable();
            $table->string('featured_image_url')->nullable();
            $table->string('canonical_url')->nullable();
            $table->text('code_head')->nullable();
            $table->text('code_foot')->nullable();

            //$table->unique(['blog_id', 'slug']);

            $table->index('blog_id');
            $table->index(['blog_id', 'created_at']);
            $table->index(['blog_id', 'updated_at']);
            $table->index(['blog_id', 'published_at']);
            $table->index(['blog_id', 'is_page']);
            $table->index(['blog_id', 'is_featured']);
            $table->index(['blog_id', 'featured_image_url']);
            $table->index(['blog_id', 'canonical_url']);
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
