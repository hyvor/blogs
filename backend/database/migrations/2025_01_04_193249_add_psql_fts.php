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
        // Using search on titlte and description
        DB::statement("CREATE INDEX searchtext_english ON post_variants USING GIN (to_tsvector('english', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_arabic ON post_variants USING GIN (to_tsvector('arabic', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_armenian ON post_variants USING GIN (to_tsvector('armenian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_basque ON post_variants USING GIN (to_tsvector('basque', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_catalan ON post_variants USING GIN (to_tsvector('catalan', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_danish ON post_variants USING GIN (to_tsvector('danish', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_dutch ON post_variants USING GIN (to_tsvector('dutch', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_finnish ON post_variants USING GIN (to_tsvector('finnish', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_french ON post_variants USING GIN (to_tsvector('french', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_german ON post_variants USING GIN (to_tsvector('german', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_greek ON post_variants USING GIN (to_tsvector('greek', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_hindi ON post_variants USING GIN (to_tsvector('hindi', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_hungarian ON post_variants USING GIN (to_tsvector('hungarian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_idonesian ON post_variants USING GIN (to_tsvector('indonesian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_irish ON post_variants USING GIN (to_tsvector('irish', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_italian ON post_variants USING GIN (to_tsvector('italian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_lithuanian ON post_variants USING GIN (to_tsvector('lithuanian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_nepali ON post_variants USING GIN (to_tsvector('nepali', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_norwegian ON post_variants USING GIN (to_tsvector('norwegian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_portugues ON post_variants USING GIN (to_tsvector('portuguese', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_romanian ON post_variants USING GIN (to_tsvector('romanian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_russian ON post_variants USING GIN (to_tsvector('russian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_serbian ON post_variants USING GIN (to_tsvector('serbian', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_spanish ON post_variants USING GIN (to_tsvector('spanish', title || ' ' || description));");   
        DB::statement("CREATE INDEX searchtext_swedish ON post_variants USING GIN (to_tsvector('swedish', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_tamil ON post_variants USING GIN (to_tsvector('tamil', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_turkish ON post_variants USING GIN (to_tsvector('turkish', title || ' ' || description));");
        DB::statement("CREATE INDEX searchtext_yiddish ON post_variants USING GIN (to_tsvector('yiddish', title || ' ' || description));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // DB::statement("DROP TRIGGER tsvector_update_trigger ON post_variants;");
        DB::statement("DROP INDEX searchtext;");
    }
};