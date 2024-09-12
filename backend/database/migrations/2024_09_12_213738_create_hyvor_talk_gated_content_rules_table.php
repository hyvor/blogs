<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hyvor_talk_gated_content_rules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('blog_id')->unsigned();
            $table->bigInteger('tag_id')->unsigned()->nullable();
            $table->string('minimum_plan');
            $table->text('gate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hyvor_talk_gated_content_rules');
    }
};
