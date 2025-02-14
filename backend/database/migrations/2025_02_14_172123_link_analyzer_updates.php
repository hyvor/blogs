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
        \Illuminate\Support\Facades\DB::unprepared(
            <<<SQL
            DROP TYPE IF EXISTS link_analyzer_link_check_types;
            CREATE TYPE link_analyzer_link_check_types AS ENUM ('internal', 'external');
            
            ALTER TABLE link_analyzer_links
                ADD COLUMN check_type link_analyzer_link_check_types NOT NULL DEFAULT 'internal',
                ADD COLUMN ignore_reason VARCHAR(255) NULL,
                ADD COLUMN comment TEXT NULL;
        SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared(
            '
             
        '
        );
    }
};
