<?php

namespace Tests;

use MeiliSearch\Client;

class MeilisearchInefficient
{
    /**
     * This is an inefficient way to wait for all tasks to complete
     * Only use in testing
     */
    public static function waitForAllTasks() : void
    {
        $client = new Client(
            strval(config('scout.meilisearch.host')),
            strval(config('scout.meilisearch.key'))
        );

        while (
            collect($client->getTasks()->toArray()['results'])
                ->filter(fn($task) => $task['status'] === 'enqueued' || $task['status'] === 'processing')
                ->count()
            > 0
        ) {
            // 250ms
            usleep(250000);
        }
    }
}