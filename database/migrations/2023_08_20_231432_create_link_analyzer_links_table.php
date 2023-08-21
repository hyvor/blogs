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
        Schema::create('link_analyzer_links', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');
            $table->timestamp('last_checked_at');

            $table->integer('blog_id');
            $table->string('url');
            $table->smallInteger('status_code');
            $table->boolean('ignore')->default(false);

            $table->unique(['blog_id', 'url']);
            $table->index(['blog_id', 'last_checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_analyzer_links');
    }
};
