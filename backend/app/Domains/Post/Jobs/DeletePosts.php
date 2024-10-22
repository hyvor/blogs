<?php

namespace App\Domains\Post\Jobs;

use App\Domains\Post\PostRepository;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class DeletePosts implements ShouldQueue
{
    use Dispatchable;

    public function __construct(public Blog $blog)
    {
    }
    public function handle(): void
    {
        DB::transaction(function () {
            Post::where('blog_id', $this->blog->id)
                ->chunk(1000, function ($posts) {
                    foreach ($posts as $post) {
                        PostRepository::deletePost($post);
                    }
                });
        });
    }
}