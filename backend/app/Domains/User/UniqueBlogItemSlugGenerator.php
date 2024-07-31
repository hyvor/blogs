<?php

namespace App\Domains\User;

use App\Domains\Shared\UniqueBlogItemSlugGeneratorAbstract;
use App\Models\Blog;
use App\Models\User;
use Hyvor\Internal\Auth\AuthUser;

class UniqueBlogItemSlugGenerator extends UniqueBlogItemSlugGeneratorAbstract
{
    public function exists(string $slug): bool
    {
        return User::where('blog_id', $this->blog->id)->where('slug', $slug)->exists();
    }

    public static function forHyvorUser(Blog $blog, AuthUser $hyvorUser)
    {
        return static::generate($blog, [$hyvorUser->name, $hyvorUser->username, $hyvorUser->email]);
    }

    public static function forGuestUser(Blog $blog, string $name)
    {
        return static::generate($blog, [$name]);
    }
}
