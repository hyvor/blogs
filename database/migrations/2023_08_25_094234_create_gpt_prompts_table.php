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
        Schema::create('gpt_prompts', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();

            $table->integer('blog_id');
            $table->integer('post_id')->nullable();

            $table->string('prompt', 1000);
            $table->text('gpt_response')->nullable();

            $table->string('model_name')->nullable();

            $table->integer('tokens_prompt')->nullable();
            $table->integer('tokens_response')->nullable();
            $table->integer('tokens_total')->nullable();

            // index
            $table->index('post_id');
            $table->index('blog_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gpt_prompts');
    }
};
