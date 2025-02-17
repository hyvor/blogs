<?php

namespace App\Domains\LinkAnalyzer\Command;

use App\Domains\Blog\BlogService;
use App\Domains\LinkAnalyzer\Check\FullBlogAnalyzer;
use App\Domains\LinkAnalyzer\LinkStatusCheck\ExternalStatusCheckChunkDoneEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

/**
 * @codeCoverageIgnore
 */
class RunLinkAnalyzerCommand extends Command
{

    protected $signature = 'app:link-analyzer:run {subdomain}';
    protected $description = 'Run the link analyzer for the given subdomain blog and shows results';

    public function handle(): void
    {
        $subdomain = $this->argument('subdomain');
        assert(is_string($subdomain));
        $blog = BlogService::getBlogBySubdomain($subdomain);

        if (!$blog) {
            $this->error("Blog with subdomain $subdomain not found");
            return;
        }

        $this->info("Running link analyzer for subdomain: $subdomain");

        $start = microtime(true);

        Event::listen(ExternalStatusCheckChunkDoneEvent::class, function (ExternalStatusCheckChunkDoneEvent $event) {
            $this->info(
                "External check done for chunk {$event->chunkIndex} of size {$event->chunkSize} in {$event->durationSeconds} seconds"
            );
        });

        $analyze = new FullBlogAnalyzer($blog);
        $analyze->analyze();

        $this->info("Posts: {$analyze->postsCount}");
        $this->info("Links: {$analyze->linksCount}");
        $this->info("Links ok: {$analyze->linksOkCount}");
        $this->info("Links broken: {$analyze->linksBrokenCount}");
        $this->info("Links risky: {$analyze->linksRiskyCount}");
        $this->info("Links redirect: {$analyze->linksRedirectCount}");
        $this->info("Links ignored: {$analyze->linksIgnoredCount}");

        $end = microtime(true);
        $this->info("Time: " . ($end - $start) . " seconds");
    }

}