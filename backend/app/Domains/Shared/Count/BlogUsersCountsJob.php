<?php

namespace App\Domains\Shared\Count;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BlogUsersCountsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;

    public function __construct(public Blog $blog)
    {
    }

    public function handle() : void
    {
        $users = User::where('blog_id', $this->blog->id)->count();
        $this->blog->setCount('users', $users);
    }

    public function uniqueId() : int
    {
        return $this->blog->id;
    }
}
