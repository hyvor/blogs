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
        Schema::create('blog_variants', function (Blueprint $table) {
            $table->id();
            // $table->timestamps();

             // connections
             $table->bigInteger('blog_id')->index(); 
             $table->bigInteger('language_id')->index(); 
 
             // data
             $table->string('name', config('limits.max_blog_name_length'))->nullable();
             $table->string('description', config('limits.max_blog_description_length'))->nullable();
 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_variants');
    }
};
