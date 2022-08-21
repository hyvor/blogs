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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('blog_id');

            // data
            $table->enum('status', ['active', 'past_due', 'deleted']);
            $table->enum('plan', ['A', 'B', 'C', 'D', 'E']);
            $table->enum('frequency', ['monthly', 'yearly']);
            $table->timestamp('ends_at')->nullable();

            $table->json('meta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
