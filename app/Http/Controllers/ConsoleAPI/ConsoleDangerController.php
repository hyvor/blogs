<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Blog\Jobs\DeleteBlogJob;
use App\Models\Blog;

class ConsoleDangerController
{
    public function delete(Blog $blog)
    {
        DeleteBlogJob::dispatch($blog);
    }
}
