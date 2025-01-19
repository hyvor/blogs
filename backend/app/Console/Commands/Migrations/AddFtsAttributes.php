<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddFtsAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-fts-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add and init all necessary attributes for PSQL FTS search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
