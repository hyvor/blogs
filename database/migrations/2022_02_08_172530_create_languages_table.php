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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('blog_id');

            // data
            /**
             * code is a valid HTML lang attribute value
             * https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang
             * 
             * Max length:
             * Language subtag (3)
             * Script subtag (4)
             * Region subtag (3)
             * two dashes (2)
             * = 12
             */
            $table->string('code', 12); // en|en-US|etc...
            $table->string('name');
            $table->boolean('is_primary')->default(false);

            // indexes
            $table->unique(['blog_id', 'code']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
};
