<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Blog\BlogService;
use App\Models\Blog;

class ConsoleDangerController
{

    public function delete(Blog $blog)
    {
        dispatch(fn () => app(BlogService::class)->deleteBlog($blog));
    }

    public function reset(Blog $blog)
    {
        dispatch(fn () => app(BlogService::class)->resetBlog($blog));
    }

}