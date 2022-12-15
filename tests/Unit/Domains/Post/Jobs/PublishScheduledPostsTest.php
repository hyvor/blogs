<?php

namespace Tests\Unit\Domains\Post\Jobs;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Jobs\PublishScheduledPosts;
use Tests\Unit\Helpers\SchedulerFake;

it('is scheduled', function () {
    SchedulerFake::assertJobScheduled(
        PublishScheduledPosts::class,
        fn ($caller) => $caller->everyFiveMinutes()
    );
});

it('publishes scheduled posts', function () {
    $blog = blog();

    $draft = postWithVariant(['blog_id' => $blog], ['status' => 'draft']);
    $scheduledLater = postWithVariant(['blog_id' => $blog, 'published_at' => now()->addDays(7)], ['status' => 'scheduled']);
    $scheduledEarly = postWithVariant(['blog_id' => $blog, 'published_at' => now()->subHour()], ['status' => 'scheduled']);
    $published = postWithVariant(['blog_id' => $blog], ['status' => 'published']);

    (new PublishScheduledPosts())->handle();

    expect($draft->variants[0]->status)->toBe(PostStatusEnum::DRAFT);
    expect($scheduledLater->variants[0]->status)->toBe(PostStatusEnum::SCHEDULED);
    expect($scheduledEarly->variants[0]->status)->toBe(PostStatusEnum::PUBLISHED);
    expect($published->variants[0]->status)->toBe(PostStatusEnum::PUBLISHED);
});
