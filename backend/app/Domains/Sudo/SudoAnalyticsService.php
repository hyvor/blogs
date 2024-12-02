<?php

namespace App\Domains\Sudo;

use App\Models\Blog;

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

    public static function getBlogByMonth(): array
    {
        return Blog::selectRaw('YEAR(created_at) year, MONTH(created_at) month, COUNT(*) count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->toArray();
    }
}
