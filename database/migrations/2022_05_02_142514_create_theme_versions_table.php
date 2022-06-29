<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('theme_versions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('theme_id');

            // data
            $table->string('version');
            $table->string('preview_subdomain')->nullable();

            $table->unique(['theme_id', 'version']);
        });

        // https://stackoverflow.com/a/20099781/9059939
        // upto 4GB
        DB::statement("ALTER TABLE theme_versions ADD zip LONGBLOB NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_versions');
    }
};
