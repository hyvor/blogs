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
        DB::statement("ALTER TABLE post_variants ADD COLUMN searchtext TSVECTOR");
        // Using search on titlte and description
        DB::statement("UPDATE post_variants SET searchtext = to_tsvector('english', title || '' || description)");
        DB::statement("CREATE INDEX searchtext_gin ON post_variants USING GIN(searchtext)");
        DB::statement("CREATE TRIGGER ts_searchtext BEFORE INSERT OR UPDATE ON post_variants FOR EACH ROW EXECUTE PROCEDURE tsvector_update_trigger('searchtext', 'pg_catalog.english', 'title', 'description')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS tsvector_update_trigger ON post_variants");
        DB::statement("DROP INDEX IF EXISTS searchtext_gin");
        DB::statement("ALTER TABLE post_variants DROP COLUMN searchtext");
    }
};
