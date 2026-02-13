<?php

namespace Tests\Unit\Domains\Billing\Usage;

use App\Domains\Billing\UsageService;
use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Models\AutoTranslation;
use App\Models\Blog;
use App\Models\GptPrompt;
use Tests\TestCase;

class UsageServiceTest extends TestCase
{
    public function testUserUsage(): void
    {
        $usage = $this->app->make(UsageService::class);

        $blog1 = Blog::factory()->create(['organization_id' => 1, 'counts' => ['users' => 5]]);
        $blog2 = Blog::factory()->create(['organization_id' => 1, 'counts' => ['users' => 10]]);
        $blog2 = Blog::factory()->create(['organization_id' => 1]);
        $blog2 = Blog::factory()->create(['organization_id' => 1, 'counts' => ['media' => 100]]);

        Blog::factory()->create(['organization_id' => 2, 'counts' => ['users' => 20]]);

        $count = $usage->getUsersUsage(1);

        $this->assertEquals(15, $count);
    }

    public function testUserUsageWithoutBlogs(): void
    {
        $usage = $this->app->make(UsageService::class);
        $count = $usage->getUsersUsage(1);
        $this->assertEquals(0, $count);
    }

    public function testStorageUsage(): void
    {
        $usage = $this->app->make(UsageService::class);

        // 4 blogs for user
        Blog::factory()->create(['organization_id' => 1, 'counts' => ['media' => 5_000]]);
        Blog::factory()->create(['organization_id' => 1, 'counts' => ['media' => 10_000]]);
        Blog::factory()->create(['organization_id' => 1]);
        Blog::factory()->create(['organization_id' => 1, 'counts' => ['users' => 50]]);

        // other user
        Blog::factory()->create(['organization_id' => 2, 'counts' => ['users' => 20]]);

        $count = $usage->getStorageUsageBytes(1);

        $this->assertEquals(15000, $count);
    }

    public function testStorageUsageWithoutBlogs(): void
    {
        $usage = $this->app->make(UsageService::class);
        $count = $usage->getStorageUsageBytes(1);
        $this->assertEquals(0, $count);
    }

    public function testAutoTranslateCharsUsage(): void
    {
        $this->travelTo('2025-02-20');
        $usage = $this->app->make(UsageService::class);

        // 4 blogs for user
        $blog1 = Blog::factory()->create(['organization_id' => 1]);

        AutoTranslation::create([
            'blog_id' => $blog1->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 1000,
        ]);

        AutoTranslation::create([
            'blog_id' => $blog1->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 2000,
        ]);

        // old
        AutoTranslation::create([
            'blog_id' => $blog1->id,
            'created_at' => '2025-01-01',
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 1000,
        ]);

        $blog2 = Blog::factory()->create(['organization_id' => 1]);

        AutoTranslation::create([
            'blog_id' => $blog2->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 3000,
        ]);

        // other user
        $blog3 = Blog::factory()->create(['organization_id' => 2]);

        AutoTranslation::create([
            'blog_id' => $blog3->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 3000,
        ]);

        $count = $usage->getAutoTranslateCharsUsageThisMonth(1);

        $this->assertEquals(6000, $count);
    }

    public function testAutoTranslateCharsUsageWithoutBlogs(): void
    {
        $usage = $this->app->make(UsageService::class);
        $count = $usage->getAutoTranslateCharsUsageThisMonth(1);
        $this->assertEquals(0, $count);
    }

    public function testAiTokensUsage(): void
    {
        $this->travelTo('2025-02-20');
        $usage = $this->app->make(UsageService::class);

        // 4 blogs for user
        $blog = Blog::factory()->create(['organization_id' => 1]);

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

        $blog2 = Blog::factory()->create(['organization_id' => 1]);

        GptPrompt::factory()->create([
            'blog_id' => $blog2->id,
            'tokens_total' => 1000,
        ]);

        // other user
        $blog3 = Blog::factory()->create(['organization_id' => 2]);

        GptPrompt::factory()->create([
            'blog_id' => $blog3->id,
            'tokens_total' => 1000,
        ]);

        $count = $usage->getAiTokensUsage(1);

        $this->assertEquals(4000, $count);
    }

    public function testAiTokensUsageWithoutBlogs(): void
    {
        $usage = $this->app->make(UsageService::class);
        $count = $usage->getAiTokensUsage(1);
        $this->assertEquals(0, $count);
    }
}
