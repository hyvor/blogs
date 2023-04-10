<?php

namespace Tests\Unit\Domains\Integrations\EmailOctopus;

use App\Domains\Integrations\EmailOctopus\EmailOctopusSyncJob;
use App\Models\NewsletterSyncedUser;
use Illuminate\Support\Facades\Http;

it('sync users', function() {

    Http::fake([
        'emailoctopus.com/api/1.6/lists/*' => Http::response(),
    ]);

    $blog1 = blog();
    $blog2 = blog();

    $job = new EmailOctopusSyncJob();
    $job->handle();

    $syncedUsers = NewsletterSyncedUser::all();

    expect($syncedUsers->count())->toBe(2);
    expect($syncedUsers->pluck('hyvor_user_id'))->toContain($blog1->hyvor_user_id);
    expect($syncedUsers->pluck('hyvor_user_id'))->toContain($blog2->hyvor_user_id);

});

it('does not sync users that are already synced', function() {

    Http::fake([
        'emailoctopus.com/api/1.6/lists/*' => Http::response(),
    ]);

    $blog1 = blog();
    $blog2 = blog();

    NewsletterSyncedUser::create([
        'hyvor_user_id' => $blog1->hyvor_user_id
    ]);

    $job = new EmailOctopusSyncJob();
    $job->handle();

    $syncedUsers = NewsletterSyncedUser::all();

    Http::assertSentCount(1);

    expect($syncedUsers->count())->toBe(2);

});