<?php declare(strict_types=1);

namespace Tests\Feature\Commands\Migrations;

use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Models\Subscription;

it('upgrades all blogs', function() {

    $blog1 = blog();
    $blog2 = blog();
    $blog3 = blog();

    $blog1->subscriptions()->create([
        'plan' => 'starter',
        'frequency' => 'monthly',
        'status' => 'active',
    ]);

    $this->artisan('migrate:upgrade-all-blogs');

    $allSubscriptions = Subscription::all();

    expect($allSubscriptions->count())->toBe(3);

    expect($allSubscriptions[0]->blog_id)->toBe($blog1->id);
    expect($allSubscriptions[0]->plan)->toBe(SubscriptionPlanEnum::STARTER);

    expect($allSubscriptions[1]->blog_id)->toBe($blog2->id);
    expect($allSubscriptions[1]->plan)->toBe(SubscriptionPlanEnum::STARTER);
    expect($allSubscriptions[1]->status)->toBe(SubscriptionStatusEnum::ACTIVE);

});