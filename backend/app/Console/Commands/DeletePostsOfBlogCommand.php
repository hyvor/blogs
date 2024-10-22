<?php

namespace App\Console\Commands;

use App\Domains\Post\Jobs\DeletePosts;
use App\Models\Blog;
use Illuminate\Console\Command;

class DeletePostsOfBlogCommand extends Command
{
    protected $signature = 'delete:posts {--subdomain=}';

    public function handle(): void
    {
        $subdomain = $this->option('subdomain');
        $blog = Blog::where('subdomain', $subdomain)->first();

        if (!$blog) {
            $this->error('Blog not found');
            return;
        }
        if ($this->confirm('Are you sure you want to delete all posts of the blog:' . $blog->subdomain . '?')) {
            DeletePosts::dispatch($blog);
            $this->info('Posts deleted');
        }
    }

}