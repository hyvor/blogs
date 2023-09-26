<?php declare(strict_types=1);

namespace App\Domains\App\Marketing;

use App\Models\Blog;
use Illuminate\Support\Facades\Cache;

class MarketingService
{

    // used in reviews.blade.php
    public static function getBlogsCount() : int
    {
        $key = 'landing_page_blogs_count';
        $count = Cache::get($key);

        if ($count)
            return intval($count);

        $count = Blog::where('type', 'default')->count();
        Cache::put($key, $count, 60 * 60 * 24);

        return $count;
    }


}