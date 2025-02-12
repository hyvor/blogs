<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Domains\LinkAnalyzer\Check\DispatchAllChecksJob;
use App\Models\LinkAnalyzerCheck;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Support\Facades\Queue;

it('dispatches all jbos', function () {
    Queue::fake();

    BillingFake::enable(license: function (int $userId) {
        if ($userId === 2) {
            return null;
        }
        if ($userId === 3) {
            return new BlogsLicense(analyses: false);
        }
        return new BlogsLicense();
    });

    $blog = blog(['hyvor_user_id' => 1]);

    $blogWithoutLinkAnalysis = blog();
    $blogWithoutLinkAnalysis->setMeta('link_analysis_enabled', false);

    $blogWithRecentCheck = blog();
    LinkAnalyzerCheck::create([
        'blog_id' => $blogWithRecentCheck->id,
        'created_at' => now()->subDays(13)
    ]);

    $allBlogs = collect([$blog, $blogWithoutLinkAnalysis, $blogWithRecentCheck]);
    $blogWithNoLicense = blog(['hyvor_user_id' => 2]);
    $blogWithNoAnalysesLicense = blog(['hyvor_user_id' => 3]);

    (new DispatchAllChecksJob())->handle();

    Queue::assertPushed(AnalyzeAllLinksJob::class, function ($job) use ($blog) {
        expect($job->blog->id)->toBe($blog->id);
        return true;
    });
});
