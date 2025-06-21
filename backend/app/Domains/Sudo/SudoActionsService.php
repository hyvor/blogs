<?php

namespace App\Domains\Sudo;

use App\Models\Blog;
use Carbon\Carbon;

class SudoActionsService
{
    public static function updateBlogTrial(Blog $blog, int $trialEndsAt): void
    {
        $blog->update(attributes: [
            'trial_ends_at' => Carbon::createFromTimestamp($trialEndsAt)
        ]);
    }

    public static function blockBlog(Blog $blog): void
    {
        $blog->update(attributes: [
            'is_blocked' => true,
            'blocked_at' => Carbon::now()
        ]);
    }


    public static function unblockBlog(Blog $blog): void
    {
        $blog->update(attributes: [
            'is_blocked' => false,
            'blocked_at' => null
        ]);
    }
}
