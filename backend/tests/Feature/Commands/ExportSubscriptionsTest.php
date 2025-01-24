<?php

namespace Tests\Feature\Commands;

use App\Console\Commands\ExportSubscriptions;
use App\Models\Blog;
use App\Models\Subscription;
use Tests\TestCase;

class ExportSubscriptionsTest extends TestCase
{

    public function testAll(): void
    {

        // selected
        $subscriptionUser1 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'starter',
            'status' => 'active',
        ]);

        // not selected, overriden by paddle ID
        $subscriptionUser2 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 2]),
            'plan' => 'growth',
            'status' => 'active',
        ]);
        // selected
        $subscriptionUser2WithPaddleId = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 2]),
            'plan' => 'starter',
            'status' => 'active',
            'meta' => '{"paddle_subscription_id": "123"}',
        ]);

        $subscriptionUser3Deleted = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 3]),
            'plan' => 'starter',
            'status' => 'deleted',
        ]);


        // not selected, overriden by large plan
        $subscriptionUser4 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 4]),
            'plan' => 'growth',
            'status' => 'active',
        ]);
        // selected
        $subscriptionUser4Large = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 4]),
            'plan' => 'premium',
            'status' => 'active',
        ]);

        $command = new ExportSubscriptions();

        $this->assertCount(3, $command->getSubscriptions());
        $this->assertEquals('starter', $command->getSubscriptions()[0]->plan);
        $this->assertEquals(1, $command->getSubscriptions()[0]->userId);
        $this->assertEquals(null, $command->getSubscriptions()[0]->paddleSubscriptionId);

        $this->assertEquals('starter', $command->getSubscriptions()[1]->plan);
        $this->assertEquals(2, $command->getSubscriptions()[1]->userId);
        $this->assertEquals('123', $command->getSubscriptions()[1]->paddleSubscriptionId);

        $this->assertEquals('premium', $command->getSubscriptions()[2]->plan);
        $this->assertEquals(4, $command->getSubscriptions()[2]->userId);
        $this->assertEquals(null, $command->getSubscriptions()[2]->paddleSubscriptionId);

    }

    public function testDoesNotSelectDeletedSubscriptions(): void
    {

        $command = new ExportSubscriptions();

        $deletedSubscription = Subscription::factory()->create([
            'status' => 'deleted',
            'ends_at' => null,
        ]);

        Subscription::factory()->create([
            'status' => 'deleted',
            'ends_at' => now()->subDay(), // past, should not be selected
        ]);

        $this->assertCount(0, $command->getSubscriptions());

        Subscription::factory()->create([
            'status' => 'deleted',
            'ends_at' => now()->addDay(), // future, should be selected
        ]);

        $this->assertCount(1, $command->getSubscriptions());

    }

    public function testSelectsSubscriptionWithPaddleId(): void
    {

        $command = new ExportSubscriptions();

        $subscription1 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'starter',
            'status' => 'active',
            'meta' => '{"paddle_subscription_id": "123"}',
        ]);

        $subscription2 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'growth',
            'status' => 'active',
        ]);

        $this->assertCount(1, $command->getSubscriptions());
        $this->assertEquals('starter', $command->getSubscriptions()[0]->plan);

    }

    public function testSelectsSubscriptionWithPaddleIdReverseOrder(): void
    {

        $command = new ExportSubscriptions();

        $subscription1 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'growth',
            'status' => 'active',
        ]);

        $subscription2 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'starter',
            'status' => 'active',
            'meta' => '{"paddle_subscription_id": "123"}',
        ]);

        $this->assertCount(1, $command->getSubscriptions());
        $this->assertEquals('starter', $command->getSubscriptions()[0]->plan);

    }

    public function testSelectsLargestPlan(): void
    {

        $command = new ExportSubscriptions();

        $subscription1 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $subscription2 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'growth',
            'status' => 'active',
        ]);

        $this->assertCount(1, $command->getSubscriptions());
        $this->assertEquals('growth', $command->getSubscriptions()[0]->plan);

    }

    public function testSelectsLargestPlanWithPaddleIdAndReverseOrder(): void
    {

        $command = new ExportSubscriptions();

        $subscription1 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'growth',
            'status' => 'active',
            'meta' => '{"paddle_subscription_id": "456"}',
        ]);

        $subscription2 = Subscription::factory()->create([
            'blog_id' => Blog::factory()->create(['hyvor_user_id' => 1]),
            'plan' => 'starter',
            'status' => 'active',
            'meta' => '{"paddle_subscription_id": "123"}',
        ]);

        $this->assertCount(1, $command->getSubscriptions());
        $this->assertEquals('growth', $command->getSubscriptions()[0]->plan);
        $this->assertEquals('456', $command->getSubscriptions()[0]->paddleSubscriptionId);

    }

}
