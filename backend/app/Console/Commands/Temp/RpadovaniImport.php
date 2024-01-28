<?php

namespace App\Console\Commands\Temp;

use App\Domains\Blog\BlogService;
use App\Domains\Import\Importer\ImportJob;
use App\Domains\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Domains\Import\Sitemap\SitemapParser;
use App\Models\Import;
use Illuminate\Console\Command;

class RpadovaniImport extends Command
{

    protected $signature = 'rpadovani:import';

    public function handle() : void
    {

        $blog = BlogService::getBlogBySubdomain('new-2-import');
        $import = new Import();

        $job = new ImportJob(
            $blog,
            $import,
            new SitemapParser(
                $blog,
                'https://pastebin.com/raw/jccAYy9s',
                new PageScraperOptions(
                    contentSelector: '.import-content',
                )
            ),
            true
        );
        $job->handle();

    }

}