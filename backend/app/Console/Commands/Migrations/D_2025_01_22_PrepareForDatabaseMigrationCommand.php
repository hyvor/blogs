<?php

namespace App\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class D_2025_01_22_PrepareForDatabaseMigrationCommand extends Command
{

    public $signature = 'migrate:prepare-database-pgsql';

    public function handle() : void
    {

        $updatedAtTables = [
            'user_variants' => 'id',
            'blog_variants' => 'id',
            'link_analyzer_links' => 'created_at',
        ];

        foreach ($updatedAtTables as $table => $after) {
            $this->info('Updating ' . $table . ' table...');

            $query = <<<SQL
                ALTER TABLE $table
                ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER $after;
            SQL;

            DB::connection('mysql')->unprepared($query);
        }
    }
}
