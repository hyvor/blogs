<?php

namespace App\Console\Commands;

use App\Domains\Post\PostSearchRepository;
use Illuminate\Console\Command;

class SetupMeilisearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:meilisearch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up meilisearch attributes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        PostSearchRepository::setFilterableAttributes();
        PostSearchRepository::setSearchableAttributes();

        $this->info('Meilisearch set up');

        return 1;
    }
}
