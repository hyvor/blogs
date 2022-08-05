<?php

namespace App\Domains\Blog\Jobs;

use App\Domains\Blog\BlogService;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteBlogJob implements ShouldQueue
{

    use Dispatchable;

    public function __construct(public Blog $blog) {}

    public function handle() {
        app(BlogService::class)->deleteBlog($this->blog);
    }

}