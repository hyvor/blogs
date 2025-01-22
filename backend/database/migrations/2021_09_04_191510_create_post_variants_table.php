<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_variants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // connections
            $table->bigInteger('post_id');
            $table->bigInteger('language_id');

            $table->string('slug')->nullable();

            $table->enum('status', ['published', 'draft', 'scheduled'])->default('draft');

            $table->mediumText('content')->nullable();
            $table->mediumText('content_unsaved')->nullable();
            $table->mediumText('content_html')->nullable();
            $table->mediumText('content_text')->nullable();
            $table->string('title')->nullable();
            $table->string('description', 350)->nullable();
            $table->integer('words')->nullable();

            $table->string('seo_primary_keyword')->nullable();
            $table->json('seo_secondary_keywords')->nullable();

            $table->json('link_analysis')->nullable();

            $table->unique(['post_id', 'language_id']);
            $table->unique(['language_id', 'slug']);

            $table->index('post_id');
            $table->index('language_id');
            $table->index('status');
            $table->index('words');
        });

        # add columns for full text search
        DB::unprepared("
            ALTER TABLE post_variants ADD COLUMN ts_language regconfig DEFAULT 'simple';
            
            ALTER TABLE post_variants
            ADD COLUMN ts tsvector
            GENERATED ALWAYS AS
            (
                to_tsvector(
                    ts_language,
                    COALESCE(title, '') || ' ' ||
                    COALESCE(description, '') || ' ' ||
                    COALESCE(slug, '') || ' ' ||
                    COALESCE(content_text, '') 
                )
            ) STORED;

            CREATE INDEX ts_idx ON post_variants USING GIN (ts);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts_languages');
    }
};
