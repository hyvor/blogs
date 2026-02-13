<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Domains\LinkAnalyzer\Check\DispatchAllChecksJob;
use App\Models\LinkAnalyzerCheck;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Illuminate\Support\Facades\Queue;

it('dispatches all jbos', function () {
    Queue::fake();

    $blogLicenseWithoutAnalyse = BlogsLicense::trial();
    $blogLicenseWithoutAnalyse->analyses = false;

    BillingFake::enable([
            1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, BlogsLicense::trial()),
            2 => new ResolvedLicense(ResolvedLicenseType::NONE),
            3 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $blogLicenseWithoutAnalyse)
    ]);

    $blog = blog(['organization_id' => 1]);

    $blogWithoutLinkAnalysis = blog();
    $blogWithoutLinkAnalysis->setMeta('link_analysis_enabled', false);

    $blogWithRecentCheck = blog();
    LinkAnalyzerCheck::create([
        'blog_id' => $blogWithRecentCheck->id,
        'created_at' => now()->subDays(13)
    ]);

    $allBlogs = collect([$blog, $blogWithoutLinkAnalysis, $blogWithRecentCheck]);
    $blogWithNoLicense = blog(['organization_id' => 2]);
    $blogWithNoAnalysesLicense = blog(['organization_id' => 3]);

    (new DispatchAllChecksJob())->handle();

    Queue::assertPushed(AnalyzeAllLinksJob::class, function ($job) use ($blog) {
        expect($job->blog->id)->toBe($blog->id);
        return true;
    });
});
