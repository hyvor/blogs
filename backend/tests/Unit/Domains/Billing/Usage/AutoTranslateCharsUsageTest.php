<?php

namespace Tests\Unit\Domains\Billing\Usage;

use App\Domains\Billing\Usage\AutoTranslateCharsUsage;
use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Models\AutoTranslation;
use App\Models\Blog;
use Tests\TestCase;

class AutoTranslateCharsUsageTest extends TestCase
{

    public function testUsageOfUsers(): void
    {
        $this->travelTo('2025-02-20');
        $usage = $this->app->make(AutoTranslateCharsUsage::class);

        // 4 blogs for user
        $blog1 = Blog::factory()->create(['hyvor_user_id' => 1]);

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

        $blog2 = Blog::factory()->create(['hyvor_user_id' => 1]);

        AutoTranslation::create([
            'blog_id' => $blog2->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 3000,
        ]);

        // other user
        $blog3 = Blog::factory()->create(['hyvor_user_id' => 2]);

        AutoTranslation::create([
            'blog_id' => $blog3->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 3000,
        ]);

        $count = $usage->usageOfUser(1);

        $this->assertEquals(6000, $count);
    }

    public function testUsageOfUsersWithoutBlogs(): void
    {
        $usage = $this->app->make(AutoTranslateCharsUsage::class);
        $count = $usage->usageOfUser(1);
        $this->assertEquals(0, $count);
    }


    public function testUsageOfResource(): void
    {

        $this->travelTo('2025-02-20');
        $usage = $this->app->make(AutoTranslateCharsUsage::class);
        $blog = Blog::factory()->create();

        AutoTranslation::create([
            'blog_id' => $blog->id,
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 3000,
        ]);

        // old
        AutoTranslation::create([
            'blog_id' => $blog->id,
            'created_at' => '2025-01-01',
            'source_lang' => DeepLSourceLangEnum::EN,
            'target_lang' => DeepLTargetLangEnum::FR,
            'chars' => 1000,
        ]);

        $count = $usage->usageOfResource($blog->id);
        $this->assertEquals(3000, $count);
    }


}
