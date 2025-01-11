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
        DB::statement("CREATE INDEX searchtext ON post_variants USING GIN (to_tsvector('simple', title || ' ' || description));");
        //DB::statement("CREATE TRIGGER tsvector_update_trigger BEFORE INSERT OR UPDATE ON post_variants FOR EACH ROW EXECUTE FUNCTION tsvector_update_trigger('searchtext', 'pg_catalog.simple', 'title', 'description');");
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