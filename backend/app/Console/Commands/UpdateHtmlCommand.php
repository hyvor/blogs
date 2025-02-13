<?php

namespace App\Console\Commands;

use App\Domains\Blog\Jobs\UpdateContentHtmlOfAllPostsJob;
use App\Models\Blog;
use Illuminate\Console\Command;

class UpdateHtmlCommand extends Command
{

    protected $signature = 'blog:update-html {subdomain}';

    protected $description = 'Update content html of all posts in a blog';

    public function handle(): void
    {

        $subdomain = $this->argument('subdomain');
        assert(is_string($subdomain));

        $blog = Blog::where('subdomain', $subdomain)->first();

        if (!$blog) {
            $this->error('Blog not found');
            return;
        }

        $this->info('Updating content html of all posts...');

        $job = new UpdateContentHtmlOfAllPostsJob(
            $blog,
            onProgress: function(int $count, int $firstId, int $lastId) {
                $this->info("Updated $count posts. First id: $firstId, Last id: $lastId");
            }
        );
        $job->handle();

    }

}
