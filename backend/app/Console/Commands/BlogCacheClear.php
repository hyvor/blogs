<?php

namespace App\Console\Commands;

use App\Domains\Blog\BlogService;
use App\Domains\Cache\CacheService;
use App\Models\Blog;
use Illuminate\Console\Command;

class BlogCacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blogcache:clear {subdomain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cache of a blog or all';

    public function handle() : void
    {
        $subdomain = strval($this->argument('subdomain'));

        $cacheService = new CacheService();

        if ($subdomain) {

            $blog = BlogService::getBlogBySubdomain($subdomain);

            if (!$blog) {
                $this->error('Blog not found');
                return;
            }

            $cacheService->blog($blog)->clearAllCache();

            $this->info("Cleared cache of the blog $subdomain");

        } else {

            $blogs = Blog::select('id')->get();

            $this->info('Clearing cache of '.count($blogs).' blogs');

            foreach ($blogs as $blog) {
                $cacheService->blog($blog)->clearAllCache();
            }

            $this->info('Cache cleared!');
        }

    }
}
