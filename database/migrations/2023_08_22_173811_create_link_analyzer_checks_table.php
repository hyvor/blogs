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
        Schema::create('link_analyzer_checks', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->bigInteger('blog_id');

            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('error')->nullable();

            $table->integer('posts_count')->nullable();
            $table->integer('post_variants_count')->nullable();

            $table->integer('links_total_count')->nullable();
            $table->integer('links_ok_count')->nullable();
            $table->integer('links_broken_count')->nullable();
            $table->integer('links_redirect_count')->nullable();
            $table->integer('links_ignored_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_analyzer_checks');
    }
};
