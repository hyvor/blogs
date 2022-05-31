<?php

namespace App\Domains\User;

use App\Models\User;
use Hyvor\HyvorConnecter\HyvorUser;
use Illuminate\Support\Collection;

/**
 * A combination of User + Blog models
 * Usually, these methods are related to the Console
 */
class UserBlogRepository
{

    public static function getBlogsOfUser(HyvorUser $hyvorUser): Collection
    {
        return User::where('hyvor_user_id', $hyvorUser->id)
            ->where('status', 'active')
            ->orderBy('sort', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->with('blog', 'blog.subscriptions')
            ->get();
    }

    /**
    * To sort the order displayed of blogs displayed in the console
    * $arr = [blogId, blogId] in the correct sort
    * @param int[] $arr
    */
    public static function changeBlogSorts(HyvorUser $user, array $arr): void
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