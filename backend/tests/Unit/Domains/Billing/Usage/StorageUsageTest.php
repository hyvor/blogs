<?php

namespace Tests\Unit\Domains\Billing\Usage;

use App\Domains\Billing\Usage\StorageUsage;
use App\Models\Blog;
use Tests\TestCase;

class StorageUsageTest extends TestCase
{

    public function testUsageOfUsers(): void
    {
        $usage = $this->app->make(StorageUsage::class);

        // 4 blogs for user
        Blog::factory()->create(['hyvor_user_id' => 1, 'counts' => ['media' => 5_000]]);
        Blog::factory()->create(['hyvor_user_id' => 1, 'counts' => ['media' => 10_000]]);
        Blog::factory()->create(['hyvor_user_id' => 1]);
        Blog::factory()->create(['hyvor_user_id' => 1, 'counts' => ['users' => 50]]);

        // other user
        Blog::factory()->create(['hyvor_user_id' => 2, 'counts' => ['users' => 20]]);

        $count = $usage->usageOfUser(1);

        $this->assertEquals(15000, $count);
    }

    public function testUsageOfUsersWithoutBlogs(): void
    {
        $usage = $this->app->make(StorageUsage::class);
        $count = $usage->usageOfUser(1);
        $this->assertEquals(0, $count);
    }


    public function testUsageOfResource(): void
    {
        $usage = $this->app->make(StorageUsage::class);
        $blog = Blog::factory()->create(['counts' => ['media' => 5_000]]);
        $count = $usage->usageOfResource($blog->id);
        $this->assertEquals(5_000, $count);
    }


}
