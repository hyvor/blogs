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

            $table->integer('blog_id');
            $table->integer('post_id');

            $table->string('prompt');
            $table->string('gpt_response')->nullable();

            $table->string('model_name')->nullable();

            $table->integer('tokens_prompt')->nullable();
            $table->integer('tokens_response')->nullable();
            $table->integer('tokens_total')->nullable();
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
