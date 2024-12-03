<?php

namespace App\Domains\Sudo;


use App\Models\Blog;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;

class SudoAnalyticsService
{
    // ================================ BLOGS ================================

    public static function getBlogTotal(): int
    {
        return Blog::count();
    }

    public static function getTrialBlogs(): int
    {
        return Blog::where('trial_ends_at', '>', now())->count();
    }

    public static function getPaidBlogs(): int
    {
        // Join with subscriptions table to get only blogs with active subscriptions
        return Blog::join('subscriptions', 'blogs.id', '=', 'subscriptions.blog_id')
            ->where('subscriptions.ends_at', '>', now())
            ->count();
    }

    public static function getBlogByMonth(): mixed
    {
        return Blog::selectRaw('count(*) as count, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(fn ($blog) => $blog->count);
    }
}
