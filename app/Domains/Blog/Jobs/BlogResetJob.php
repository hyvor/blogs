<?php

namespace App\Domains\Blog\Jobs;

use App\Domains\Blog\BlogService;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlogResetJob implements ShouldQueue
{

    use Dispatchable, SerializesModels;

    public function __construct(private Blog $blog) {}

    public function handle() {
        BlogService::resetBlog($this->blog);
    }

}