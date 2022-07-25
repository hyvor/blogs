<?php

namespace App\Domains\Post\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class PublishScheduledPosts implements ShouldQueue, ShouldBeUnique
{

    public function handle()
    {

        $time = now();
        DB::statement('
            UPDATE post_variants as pv 
            SET status = ?
            WHERE pv.status = ? AND (
                SELECT p.published_at FROM posts as p
                WHERE p.id = pv.post_id
                LIMIT 1
            ) <= ?
        ', ['published', 'scheduled', $time]);

    }


}