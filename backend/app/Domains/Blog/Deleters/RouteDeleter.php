<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\Route;
use Illuminate\Database\Eloquent\Model;

class RouteDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : ?Model
    {
        Route::where('blog_id', $this->blog->id)->delete();
    }
}
