<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer\Check;

use App\Domains\Billing\LicenseService;
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

            $license = LicenseService::getLicense($blog);

            // no license
            if (!$license) {
                continue;
            }
            // analyses are not available in the blog's plan
            if ($license->analyses === false) {
                continue;
            }

            dispatch(new AnalyzeAllLinksJob($blog));

            // 100ms
            usleep(100000);

        }

    }

}
