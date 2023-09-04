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
            $table->integer('post_variant_id');

            $table->string('url');
            $table->string('full_url'); // this is the checked URL
            $table->smallInteger('status_code');
            $table->boolean('ignore')->default(false);

            $table->unique(['post_variant_id', 'url']);
            $table->index(['url']);
            $table->index(['post_variant_id', 'last_checked_at']);
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
