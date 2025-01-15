<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE post_variants ADD COLUMN ts_language TEXT");
        DB::statement("ALTER TABLE post_variants ADD COLUMN content_text VARCHAR");
        // Using search on titlte and description
        DB::statement("CREATE INDEX searchtext_english ON post_variants USING GIN (to_tsvector('english', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_arabic ON post_variants USING GIN (to_tsvector('arabic', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_armenian ON post_variants USING GIN (to_tsvector('armenian', title || ' ' || description  || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_basque ON post_variants USING GIN (to_tsvector('basque', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_catalan ON post_variants USING GIN (to_tsvector('catalan', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_danish ON post_variants USING GIN (to_tsvector('danish', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_dutch ON post_variants USING GIN (to_tsvector('dutch', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_finnish ON post_variants USING GIN (to_tsvector('finnish', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_french ON post_variants USING GIN (to_tsvector('french', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_german ON post_variants USING GIN (to_tsvector('german', title || ' ' || description || ' ' || slug || ' ' ||  content_text));");
        DB::statement("CREATE INDEX searchtext_greek ON post_variants USING GIN (to_tsvector('greek', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_hindi ON post_variants USING GIN (to_tsvector('hindi', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_hungarian ON post_variants USING GIN (to_tsvector('hungarian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_idonesian ON post_variants USING GIN (to_tsvector('indonesian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_irish ON post_variants USING GIN (to_tsvector('irish', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_italian ON post_variants USING GIN (to_tsvector('italian', title || ' ' || description || ' ' || slug || ' ' ||  content_text));");
        DB::statement("CREATE INDEX searchtext_lithuanian ON post_variants USING GIN (to_tsvector('lithuanian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_nepali ON post_variants USING GIN (to_tsvector('nepali', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_norwegian ON post_variants USING GIN (to_tsvector('norwegian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_portuguese ON post_variants USING GIN (to_tsvector('portuguese', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_romanian ON post_variants USING GIN (to_tsvector('romanian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_russian ON post_variants USING GIN (to_tsvector('russian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_serbian ON post_variants USING GIN (to_tsvector('serbian', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_spanish ON post_variants USING GIN (to_tsvector('spanish', title || ' ' || description || ' ' || slug || ' ' || content_text));");   
        DB::statement("CREATE INDEX searchtext_swedish ON post_variants USING GIN (to_tsvector('swedish', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_tamil ON post_variants USING GIN (to_tsvector('tamil', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_turkish ON post_variants USING GIN (to_tsvector('turkish', title || ' ' || description || ' ' || slug || ' ' || content_text));");
        DB::statement("CREATE INDEX searchtext_yiddish ON post_variants USING GIN (to_tsvector('yiddish', title || ' ' || description || ' ' || slug || ' ' || content_text));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE post_variants DROP COLUMN ts_language");
        DB::statement("ALTER TABLE post_variants DROP COLUMN content_text");
        DB::statement("DROP INDEX searchtext_english");
        DB::statement("DROP INDEX searchtext_arabic");
        DB::statement("DROP INDEX searchtext_armenian");
        DB::statement("DROP INDEX searchtext_basque");
        DB::statement("DROP INDEX searchtext_catalan");
        DB::statement("DROP INDEX searchtext_danish");
        DB::statement("DROP INDEX searchtext_dutch");
        DB::statement("DROP INDEX searchtext_finnish");
        DB::statement("DROP INDEX searchtext_french");
        DB::statement("DROP INDEX searchtext_german");
        DB::statement("DROP INDEX searchtext_greek");
        DB::statement("DROP INDEX searchtext_hindi");
        DB::statement("DROP INDEX searchtext_hungarian");
        DB::statement("DROP INDEX searchtext_idonesian");
        DB::statement("DROP INDEX searchtext_irish");
        DB::statement("DROP INDEX searchtext_italian");
        DB::statement("DROP INDEX searchtext_lithuanian");
        DB::statement("DROP INDEX searchtext_nepali");
        DB::statement("DROP INDEX searchtext_norwegian");
        DB::statement("DROP INDEX searchtext_portuguese");
        DB::statement("DROP INDEX searchtext_romanian");
        DB::statement("DROP INDEX searchtext_russian");
        DB::statement("DROP INDEX searchtext_serbian");
        DB::statement("DROP INDEX searchtext_spanish");
        DB::statement("DROP INDEX searchtext_swedish");
        DB::statement("DROP INDEX searchtext_tamil");
        DB::statement("DROP INDEX searchtext_turkish");
        DB::statement("DROP INDEX searchtext_yiddish");
    }
};