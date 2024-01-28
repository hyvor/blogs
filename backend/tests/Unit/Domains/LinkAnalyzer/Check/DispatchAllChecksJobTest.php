<?php declare(strict_types=1);

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Domains\LinkAnalyzer\Check\DispatchAllChecksJob;
use App\Domains\Subscription\SubscriptionService;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Support\Facades\Queue;

it('dispatches all jbos', function() {

    Queue::fake();

    $blog = blog();

    $blogWithoutLinkAnalysis = blog();
    $blogWithoutLinkAnalysis->setMeta('link_analysis_enabled', false);

    $blogWithRecentCheck = blog();
    LinkAnalyzerCheck::create([
        'blog_id' =>  $blogWithRecentCheck->id,
        'created_at' => now()->subDays(13)
    ]);

    $allBlogs = collect([$blog, $blogWithoutLinkAnalysis, $blogWithRecentCheck]);

    foreach ($allBlogs as $eachBlog) {
        SubscriptionService::createSubscription(
            $eachBlog,
            SubscriptionPlanEnum::GROWTH
        );
    }

    $blogWithNoSubscription = blog();

    (new DispatchAllChecksJob())->handle();

    Queue::assertPushed(AnalyzeAllLinksJob::class, function ($job) use ($blog) {
        expect($job->blog->id)->toBe($blog->id);
        return true;
    });

});