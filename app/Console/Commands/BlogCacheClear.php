<?php

namespace App\Console\Commands;

use App\Domains\Blog\BlogRepository;
use App\Domains\Cache\CacheRepository;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $subdomain = $this->argument('subdomain');

        if ($subdomain) {
            $blog = BlogRepository::getBlogBySubdomain($subdomain);
            CacheRepository::clearAll($blog);

            $this->info("Cleared cache of the blog $subdomain");
        } else {
            $blogs = Blog::select('id')->get();

            $this->info("Clearing cache of " . count($blogs) . " blogs");

            foreach ($blogs as $blog) {
                CacheRepository::clearAll($blog);
            }

            $this->info("Cache cleared!");
        }
    }
}
