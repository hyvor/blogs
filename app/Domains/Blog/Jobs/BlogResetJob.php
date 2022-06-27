<?php

namespace App\Domains\Blog\Jobs;

use App\Domains\Blog\BlogRepository;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlogResetJob implements ShouldQueue
{

    use Dispatchable, SerializesModels;

    public function __construct(private Blog $blog) {}

    public function handle() {
        BlogRepository::resetBlog($this->blog);
    }

}