<?php

namespace App\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class D_2025_01_22_DatabaseMigrationCommand extends Command
{

    public $signature = 'migrate:database-pgsql';

    public function handle() : void
    {
        $tablesToSkip =  [
            'migrations',
            'cache',
            'cache_locks',
            'failed_jobs',
            'jobs',

            // Pending Issues
            'theme_files'
        ];

//        if (!$this->confirm('Are you sure you want to migrate the database?')) {
//            return;
//        }

        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\'');
        $lastRun = null;

        while (true){
            $currentRun = now()->toImmutable();

            foreach ($tables as $table) {

                if (in_array($table->table_name, $tablesToSkip)) {
                    $this->info("Skipping table: {$table->table_name}");
                    continue;
                }

                $this->info("Migrating table: {$table->table_name}");

                // Get the columns of the current table
                $columns = DB::select('SELECT column_name FROM information_schema.columns WHERE table_name = ?', [$table->table_name]);
                $columnNames = array_map(fn($column) => $column->column_name, $columns);

                DB::connection('mysql')
                    ->table($table->table_name)
                    ->when($lastRun, fn($query) => $query->where('updated_at', '>=', $lastRun))
                    ->orderBy('id')
                    ->chunk(1000, function ($rows) use ($table, $columnNames) {

                        foreach ($rows as $row) {
                            $data = (array) $row;

                            // Filter data to only include existing columns
                            $filteredData = array_filter($data, fn($key) => in_array($key, $columnNames), ARRAY_FILTER_USE_KEY);

                            DB::connection('pgsql')
                                ->table($table->table_name)
                                ->updateOrInsert(
                                    ['id' => $data['id']],
                                    $filteredData
                                );
                        }
                    });
            }

            $lastRun = clone $currentRun;
            sleep(1);
        }
    }
}
