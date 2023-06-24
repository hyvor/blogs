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
        Schema::create('appsumo_codes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('code')->unique();
            $table->bigInteger('blog_id')->nullable();
            $table->timestamp('redeemed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appsumo_codes');
    }
};
