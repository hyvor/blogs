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

        $affectedRows = DB::connection('pgsql')
            ->table('post_variants')
            ->where('created_at', '<', '2025-01-26 19:43:51')
            ->where('content', 'like', '%?%')
            ->select(['id', 'content'])
            ->get();

        $unaffectedRows = DB::connection('mysql')
            ->table('post_variants')
            ->whereIn('id', $affectedRows->pluck('id')->toArray())
            ->select(['id', 'content', 'content_html', 'content_unsaved'])
            ->get();

        if ($nuke) {
            foreach ($affectedRows as $affectedRow) {
                $unaffectedRow = $unaffectedRows->firstWhere('id', $affectedRow->id);
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

                if ($lengthDifference > 10) {
                    $data = [
                        'id' => $affectedRow->id,
                        'length_difference' => $lengthDifference,
                        'unaffected_content' => $unaffectedRow->content,
                        'affected_content' => $affectedRow->content,
                    ];
                    dump($data);
                    $results[] = $data;
                }
            }
        }

        $json = json_encode($results, JSON_PRETTY_PRINT);
        if (!$json){
            dump('JSON encoding failed');
            return;
        }
        File::put('results.json', $json);
    }
}
