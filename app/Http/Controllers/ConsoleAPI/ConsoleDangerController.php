<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Blog\Jobs\BlogDeleteJob;
use App\Domains\Blog\Jobs\BlogResetJob;
use App\Models\Blog;

class ConsoleDangerController
{

    public function delete(Blog $blog)
    {
        BlogDeleteJob::dispatch($blog);
    }

    public function reset(Blog $blog)
    {
        BlogResetJob::dispatch($blog);
    }

}