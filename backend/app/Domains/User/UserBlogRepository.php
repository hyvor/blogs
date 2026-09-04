<?php

namespace App\Domains\User;

use App\Models\User;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Illuminate\Support\Collection;

/**
 * A combination of o + Blog models
 * Usually, these methods are related to the Console
 */
class UserBlogRepository
{
    /**
     * @return Collection<int, User>
     */
    public static function getBlogsOfUser(AuthUser $user, ?AuthUserOrganization $organization): Collection
    {
        if (!$organization) {
            return collect();
        }

        return User::where('users.hyvor_user_id', $user->id)
            ->join('blogs', 'blogs.id', '=', 'users.blog_id')
            ->where('blogs.organization_id', $organization->id)
            ->where('status', 'active')
            ->orderBy('sort', 'asc')
            ->orderBy('users.id', 'asc')
            ->with('blog', 'blog.subscriptions')
            ->get();
    }
}
