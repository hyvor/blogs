<?php

namespace Tests\Unit\Domains\Billing\Usage;

use App\Domains\Billing\Usage\UsersUsage;
use App\Models\Blog;
use Tests\TestCase;

class UsersUsageTest extends TestCase
{

    public function testUsageOfUsers(): void
    {
        $usage = $this->app->make(UsersUsage::class);

        $blog1 = Blog::factory()->create(['hyvor_user_id' => 1, 'counts' => ['users' => 5]]);
        $blog2 = Blog::factory()->create(['hyvor_user_id' => 1, 'counts' => ['users' => 10]]);
        $blog2 = Blog::factory()->create(['hyvor_user_id' => 1]);
        $blog2 = Blog::factory()->create(['hyvor_user_id' => 1, 'counts' => ['media' => 100]]);

        Blog::factory()->create(['hyvor_user_id' => 2, 'counts' => ['users' => 20]]);

        $count = $usage->usageOfUser(1);

        $this->assertEquals(15, $count);
    }

    public function testUsageOfUsersWithoutBlogs(): void
    {
        $usage = $this->app->make(UsersUsage::class);
        $count = $usage->usageOfUser(1);
        $this->assertEquals(0, $count);
    }


    public function testUsageOfResource()
    {
        $usage = $this->app->make(UsersUsage::class);
        $blog = Blog::factory()->create(['counts' => ['users' => 5]]);
        $count = $usage->usageOfResource($blog->id);
        $this->assertEquals(5, $count);
    }

}
