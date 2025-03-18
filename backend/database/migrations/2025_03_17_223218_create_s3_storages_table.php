<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('s3_storages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('blog_id')->unsigned();
            $table->foreign('blog_id')->references('id')->on('blogs')->onDelete('cascade');

            $table->text('endpoint_url');
            $table->text('bucket_name');
            $table->text('access_key');
            $table->text('secret_key');
            $table->text('region')->nullable();
            $table->boolean('path_style_access')->default(false);
            $table->text('cdn_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s3_storages');
    }
};
