<?php

namespace App\Console\Commands\Import;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\Importer;
use App\Domains\Import\WordPress\WordPressParser;
use App\Models\Blog;
use Illuminate\Console\Command;

class ImportWordPressCommand extends Command
{

    protected $signature = 'import:wordpress {path}';

    protected $description = 'Import WordPress XML. Path must be in the storage/app directory';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $blog = Blog::where('subdomain', 'joomla')->first();
        $path = storage_path('app/' . $this->argument('path'));
        $parser = new WordPressParser($blog, $path, new JobMessageLog($this));
        $importer = new Importer($blog, $parser, true);
        $importer->import();
    }

}