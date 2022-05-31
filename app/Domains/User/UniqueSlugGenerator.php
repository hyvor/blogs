<?php

namespace App\Domains\User;

use App\Domains\_Shared\UniqueSlugGeneratorAbstract;
use App\Models\Blog;
use App\Models\User;
use Hyvor\HyvorConnecter\HyvorUser;

class UniqueSlugGenerator extends UniqueSlugGeneratorAbstract
{

    public function exists(string $slug): bool
    {
        return User::where('blog_id', $this->blog->id)->where('slug', $slug)->exists();
    }

    public static function forHyvorUser(Blog $blog, HyvorUser $hyvorUser)
    {
        return static::generate($blog, [$hyvorUser->name, $hyvorUser->username, $hyvorUser->email]);
    }

    public static function forGuestUser(Blog $blog, string $name)
    {
        return static::generate($blog, [$name]);
    }

}