<?php

namespace Tests\Unit\Domains\Billing\Usage;

use App\Domains\Billing\Usage\AiTokensUsage;
use App\Models\Blog;
use App\Models\GptPrompt;
use Tests\TestCase;

class AiTokensUsageTest extends TestCase
{

    public function testUsageOfUsers(): void
    {
        $this->travelTo('2025-02-20');
        $usage = $this->app->make(AiTokensUsage::class);

        // 4 blogs for user
        $blog = Blog::factory()->create(['hyvor_user_id' => 1]);

        GptPrompt::factory()->create([
            'blog_id' => $blog->id,
            'tokens_total' => 1000,
        ]);

        GptPrompt::factory()->create([
            'blog_id' => $blog->id,
            'tokens_total' => 2000,
        ]);

        // old
        GptPrompt::factory()->create([
            'created_at' => '2025-01-01',
            'blog_id' => $blog->id,
            'tokens_total' => 1000,
        ]);

        $blog2 = Blog::factory()->create(['hyvor_user_id' => 1]);

        GptPrompt::factory()->create([
            'blog_id' => $blog2->id,
            'tokens_total' => 1000,
        ]);

        // other user
        $blog3 = Blog::factory()->create(['hyvor_user_id' => 2]);

        GptPrompt::factory()->create([
            'blog_id' => $blog3->id,
            'tokens_total' => 1000,
        ]);

        $count = $usage->usageOfUser(1);

        $this->assertEquals(4000, $count);
    }

    public function testUsageOfUsersWithoutBlogs(): void
    {
        $usage = $this->app->make(AiTokensUsage::class);
        $count = $usage->usageOfUser(1);
        $this->assertEquals(0, $count);
    }


    public function testUsageOfResource(): void
    {

        $this->travelTo('2025-02-20');
        $usage = $this->app->make(AiTokensUsage::class);
        $blog = Blog::factory()->create();

        GptPrompt::factory()->create([
            'blog_id' => $blog->id,
            'tokens_total' => 1250,
        ]);

        // old
        GptPrompt::factory()->create([
            'created_at' => '2025-01-01',
            'blog_id' => $blog->id,
            'tokens_total' => 1000,
        ]);

        $count = $usage->usageOfResource($blog->id);
        $this->assertEquals(1250, $count);
    }

}
