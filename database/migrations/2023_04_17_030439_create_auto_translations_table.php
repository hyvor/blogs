<?php declare(strict_types=1);

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
        Schema::create('auto_translations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('blog_id')->unsigned();

            $table->string('source_lang');
            $table->string('target_lang');
            $table->integer('chars');

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
        Schema::dropIfExists('auto_translations');
    }
};
