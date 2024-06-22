<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Database\Eloquent\Model;

class UserDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : void
    {
        UserVariant::join('users', 'users.id', '=', 'user_variants.user_id')
            ->where('users.blog_id', $this->blog->id)
            ->delete();

        User::where('blog_id', $this->blog->id)
            ->delete();
    }
}
