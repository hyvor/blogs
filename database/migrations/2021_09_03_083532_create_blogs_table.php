<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->string('ip')->nullable();

            $table->boolean('is_blocked')->default(false);

            // connections
            $table->bigInteger('hyvor_user_id')->nullable(); // hyvor user id (owner)
            $table->bigInteger('theme_version_id')->nullable();

            // data
            $table->string('subdomain')->unique();
            $table->timestamp('trial_ends_at');
            $table->enum('billing_type', ['paddle', 'shopify'])->default('paddle');
            $table->enum('integration', ['shopify'])->nullable();
            $table->enum('type', ['default', 'dev', 'preview', 'temp'])->default('default');

            $table->enum('hosting_at', ['subdomain', 'domain', 'self'])->default('subdomain');
            $table->string('hosting_domain')->nullable()->unique(); // for domain
            $table->string('hosting_url')->nullable(); // for self

            $table->json('meta')->nullable();
            $table->json('counts')->nullable();

            // index
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}
