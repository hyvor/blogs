<?php

namespace App\Console\Commands;

use App\Domains\Blog\Jobs\DeleteBlogJob;
use App\Models\Blog;
use Illuminate\Console\Command;

class DeleteBlogCommand extends Command
{
    protected $signature = 'delete:blog {--userId=} {--blogId=}';

    public function handle(): void
    {
        $userId = $this->option('userId');
        $blogId = $this->option('blogId');

        if (!$userId && !$blogId) {
            $this->error('You must provide either a userId or a blogId.');     // @codeCoverageIgnore
            return;     // @codeCoverageIgnore
        }

        if ($userId && !$this->confirm('Are you sure you want to delete all the blogs of the user with ID: ' . $userId . '?')) {
            return;     // @codeCoverageIgnore
        }

        $blogs = $userId ? Blog::where('hyvor_user_id', $userId)->get() : Blog::where('id', $blogId)->get();

        if ($blogs->isEmpty()) {
            $this->error('No blogs to delete!');        // @codeCoverageIgnore
        }

        if (!$this->confirm('Are you sure you want to delete blogs with following subdomains: ' . $blogs->pluck('subdomain')->implode(', ') . '?')) {
            return;     // @codeCoverageIgnore
        }

        foreach ($blogs as $blog) {
            $this->info( now() . ' | Deleting blog: ' . $blog->subdomain);
            $job = new DeleteBlogJob($blog);
            $job->handle();
        }
    }
}
