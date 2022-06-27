<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use App\Models\User;
use App\Models\UserVariant;

class UserDeleter implements DeleterInterface
{

    private bool $deleteOwner = false;

    public function __construct(private Blog $blog) {}

    public function withOwner()
    {
        $this->deleteOwner = true;
        return $this;
    }

    public function delete()
    {

        UserVariant::join('users', 'users.id', '=', 'user_variants.user_id')
            ->when(!$this->deleteOwner, fn ($query) => $query->where('users.role', '!=', 'owner'))
            ->where('users.blog_id', $this->blog->id)
            ->delete();

        User::where('blog_id', $this->blog->id)
            ->when(!$this->deleteOwner, fn ($query) => $query->where('role', '!=', 'owner'))
            ->delete();

    }

}