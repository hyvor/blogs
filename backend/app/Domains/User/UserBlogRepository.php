<?php

namespace App\Domains\User;

use App\Models\User;
use Hyvor\Internal\Auth\AuthUser;
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
    public static function getBlogsOfUser(AuthUser $user): Collection
    {
        return User::where('users.hyvor_user_id', $user->id)
            ->join('blogs', 'blogs.id', '=', 'users.blog_id')
            ->where('blogs.organization_id', $user->getCurrentOrganization()->id)
            ->where('status', 'active')
            ->orderBy('sort', 'ASC')
            ->orderBy('users.id', 'ASC')
            ->with('blog', 'blog.subscriptions')
            ->get();
    }

    /**
     * To sort the order displayed of blogs displayed in the console
     * $arr = [blogId, blogId] in the correct sort
     *
     * @param int[] $arr
     */
    public static function changeBlogSorts(AuthUser $user, array $arr): void
    {
        $i = 1;
        foreach ($arr as $blogId) {
            User::where('blog_id', $blogId)
                ->where('hyvor_user_id', $user->id)
                ->update([
                    'sort' => $i,
                ]);
            $i++;
        }
    }
}
