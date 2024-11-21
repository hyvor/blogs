<?php

namespace App\Console\Commands\Import;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\Importer;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Import\WordPress\WordPressParser;
use App\Models\Blog;
use Illuminate\Console\Command;

class ImportWordPressCommand extends Command
{

    protected $signature = 'import:wordpress {--path=} {--subdomain=} {--test}';

    protected $description = 'Import WordPress XML. Path must be in the storage/app directory';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $test = $this->option('test');

        $subdomain = $this->option('subdomain');
        $blog = Blog::where('subdomain', $subdomain)->first();

        if (!$blog) {
            $this->error('Blog not found');
            return;
        }

        $path = storage_path('app/' . $this->option('path'));

        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return;
        }

        $messageLog = new JobMessageLog($this);

        try {
            $parser = new WordPressParser($blog, $path, $messageLog);
        } catch (ParserException $e) {
            $this->error($e->getMessage());
            return;
        }

        if ($test) {
            $this->info('Testing import. Parsing file...');
            $parser->parse();

            $this->info('Checking for missing uploads...');
            $missingUploads = $parser->getMissingUploadsCount();
            if ($missingUploads > 0) {
                $this->error($missingUploads . ' uploads are missing');
            } else {
                $this->info('All uploads exist');
            }

            $this->info((string) json_encode([
                'posts' => count($parser->posts),
                'uploads' => count($parser->uploads),
                'duplicates_ignored' =>$parser->duplicateCount
            ]));

            return;
        }

        $importer = new Importer($blog, $parser, true);
        $importer->import();
    }

}