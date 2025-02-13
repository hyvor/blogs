<?php

namespace App\Domains\Sudo;


use App\Models\Blog;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;
use Illuminate\Support\Facades\DB;

class SudoAnalyticsService
{
    // ================================ BLOGS ================================

    public static function getBlogTotal(): int
    {
        return Blog::count();
    }

    public static function getBlog30DaysChange(): int
    {
        return Blog::where('created_at', '>', now()->subDays(30))->count();
    }

    public static function getTrialBlogs(): int
    {
        return Blog::where('trial_ends_at', '>', now())->count();
    }

    public static function getBlogByMonth(): mixed
    {
        return DB::table('blogs')
            ->selectRaw('count(*) as count, TO_CHAR(created_at, \'YYYY-MM\') AS month')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(fn(mixed $blog) => $blog->count);
    }
}
