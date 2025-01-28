<?php

namespace App\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class D_2025_01_28_FixMigrationMessCommand extends Command
{

    public $signature = 'fix:migration-mess {--nuke}';
    public function handle() : void
    {
        ini_set('memory_limit', '2048M');

        $nuke = $this->option('nuke');

        /**
         * This selects post_variants that were created before we ran the migration
         * and also contains '?' in the content which is a sign of UTF8 encoding issue
         */
        $affectedRows = DB::connection('pgsql')
            ->table('post_variants')
            ->where('created_at', '<', '2025-01-26 19:43:51')
            ->where('content', 'like', '%?%')
            ->select(['id', 'content', 'content_unsaved'])
            ->get();

        if (!$this->confirm('Do you want to fix ' . $affectedRows->count() . ' rows?')) {
            return;
        }

        /**
         * Takes the values from the old MYSQL database. These values should be correct.
         */
        $unaffectedRows = DB::connection('mysql')
            ->table('post_variants')
            ->whereIn('id', $affectedRows->pluck('id')->toArray())
            ->select(['id', 'content', 'content_html', 'content_unsaved'])
            ->get();

        if ($nuke) {
            foreach ($affectedRows as $affectedRow) {
                $unaffectedRow = $unaffectedRows->firstWhere('id', $affectedRow->id);

                if (!$unaffectedRow) {
                    $this->info('Unaffected row not found for id: ' . $affectedRow->id);
                    continue;
                }

                if (
                    $affectedRow->content === $unaffectedRow->content &&
                    $affectedRow->content_unsaved === $unaffectedRow->content_unsaved
                ) {
                    continue;
                }

                $this->info('Updating id: ' . $affectedRow->id);

                DB::connection('pgsql')
                    ->table('post_variants')
                    ->where('id', $affectedRow->id)
                    ->update([
                        'content' => $unaffectedRow->content,
                        'content_html' => $unaffectedRow->content_html,
                        'content_unsaved' => $unaffectedRow->content_unsaved,
                    ]);
            }
        }

        $results = [];

        foreach ($affectedRows as $affectedRow) {

            $unaffectedRow = $unaffectedRows->firstWhere('id', $affectedRow->id);

            if ($unaffectedRow) {
                $lengthDifference = strlen($affectedRow->content) - strlen($unaffectedRow->content);

                if (
                    $affectedRow->content === $unaffectedRow->content &&
                    $affectedRow->content_unsaved === $unaffectedRow->content_unsaved
                ) {
                    $this->info('Content is already correct for id: ' . $affectedRow->id);
                    continue;
                }

                $data = [
                    'id' => $affectedRow->id,
                    'length_difference' => $lengthDifference,
                    'unaffected_content' => $unaffectedRow->content,
                    'affected_content' => $affectedRow->content,
                ];

                $results[] = $data;

            } else {
                $this->info('Unaffected row not found for id: ' . $affectedRow->id);
            }
        }

        $json = json_encode($results, JSON_PRETTY_PRINT);
        if (!$json){
            dump('JSON encoding failed');
            return;
        }
        $this->info('Total changing rows: ' . count($results));
        File::put('results.json', $json);
    }
}
