<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\Subscription\SubscriptionService;
use App\Models\Blog;

class DispatchAllChecksJob
{

    public function handle() : void
    {

        $blogs = Blog::get();

        foreach ($blogs as $blog) {

            $lastCheck = LinkAnalyzerCheckService::getLastCheck($blog);

            // Skip if last check was less than 14 days ago
            if ($lastCheck && $lastCheck->created_at->diffInDays(now()) < 14) {
                continue;
            }

            // Skip if link analysis is disabled
            if ($blog->getMeta('link_analysis_enabled') !== true) {
                continue;
            }

            // Skip if blog is not at least on Growth plan
            if (!SubscriptionService::hasAtLeast($blog, SubscriptionPlanEnum::GROWTH))
                continue;

            dispatch(new AnalyzeAllLinksJob($blog));

        }

    }

}