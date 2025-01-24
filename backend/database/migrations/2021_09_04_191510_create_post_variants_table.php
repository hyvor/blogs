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
        $query = <<<SQL
            
            DROP TYPE IF EXISTS post_variant_status;
            CREATE TYPE post_variant_status AS ENUM ('published', 'draft', 'scheduled');
            
            CREATE TABLE post_variants (
                id serial PRIMARY KEY,
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP,
                
                post_id bigint NOT NULL,
                language_id bigint NOT NULL,
                
                slug citext NULL,
                
                status post_variant_status NOT NULL DEFAULT 'draft',
                
                content text NULL,
                content_unsaved text NULL,
                content_html text NULL,
                content_text text NULL,
                title text NULL,
                description text NULL,              -- Originally 350 char limit
                words integer NULL,
                
                seo_primary_keyword text NULL,
                seo_secondary_keywords jsonb NULL,
                
                link_analysis jsonb NULL,
                
                ts_language regconfig DEFAULT 'simple',
                ts tsvector GENERATED ALWAYS AS (
                    to_tsvector(
                        ts_language,
                        COALESCE(title, '') || ' ' ||
                        COALESCE(description, '') || ' ' ||
                        COALESCE(slug, '') || ' ' ||
                        COALESCE(content_text, '') 
                    )
                ) STORED,
                    
                UNIQUE (post_id, language_id),
                UNIQUE (language_id, slug)
                
            );

            CREATE INDEX post_variants_post_id_index ON post_variants (post_id);
            CREATE INDEX post_variants_language_id_index ON post_variants (language_id);
            CREATE INDEX post_variants_status_index ON post_variants (status);
            CREATE INDEX post_variants_words_index ON post_variants (words);

            CREATE INDEX ts_idx ON post_variants USING GIN (ts);

        SQL;

        DB::unprepared($query);
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
