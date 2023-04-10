<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up() : void
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->bigInteger('blog_id');

            $table->enum('format', ['hyvor_blogs', 'wordpress'])->default('hyvor_blogs');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('url')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exports');
    }
};
