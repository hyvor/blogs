<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsToThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs_to_themes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('blog_id');
            $table->integer('parent_theme_id');
            $table->text('header');
            $table->text('footer');
            $table->text('page_body');
            $table->text('post_body');
            $table->text('styles_1');
            $table->integer('revision_id');
            $table->boolean('is_unsaved');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs_to_themes');
    }
}
