<?php

namespace App\Console\Commands\Import;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Importer\Importer;
use App\Domains\Import\Importer\ParserException;
use App\Domains\Import\Parser\HyvorBlogsParser;
use App\Domains\Import\Parser\MediumParser;
use App\Domains\Import\Parser\Typepad\TypepadParser;
use App\Domains\Import\Parser\WordPressParser;
use App\Models\Blog;
use Illuminate\Console\Command;

class ImportCommand extends Command
{

    protected $signature = 'import {--from=} {--path=} {--subdomain=} {--test} {--noImages}';

    protected $description = 'Import WordPress XML. Path must be in the storage/app directory';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        ini_set('memory_limit', '2048M');

        $from = $this->option('from');
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

        $parserClass = match ($from) {
            'wordpress' => WordPressParser::class,
            'hb' => HyvorBlogsParser::class,
            'typepad' => TypepadParser::class,
            'medium' => MediumParser::class,
            default => throw new \Exception('Invalid import source')
        };

        try {
            $parser = new $parserClass($blog, $path, $messageLog);
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

            $this->info(
                (string)json_encode([
                    'posts' => count($parser->posts),
                    'uploads' => $parser->uploadsCount,
                    'duplicates_ignored' => $parser->duplicateCount
                ])
            );

            return;
        }

        $importer = new Importer($blog, $parser, true);
        $importer->import();
    }

}
