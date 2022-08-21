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
        Schema::create('post_variants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('post_id');
            $table->bigInteger('language_id');

            $table->enum('status', ['published', 'draft', 'scheduled'])->default('draft');

            $table->text('content')->nullable();
            $table->text('content_unsaved')->nullable();
            $table->text('content_html')->nullable();
            $table->string('title')->nullable();
            $table->string('description', 350)->default('');
            $table->integer('words')->nullable();

            $table->unique(['post_id', 'language_id']);
            $table->index('post_id');
            $table->index('language_id');
            $table->index('status');
            $table->index('words');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts_languages');
    }
};
