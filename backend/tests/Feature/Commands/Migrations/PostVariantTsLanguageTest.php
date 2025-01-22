<?php

namespace Tests\Feature\Commands\Migrations;

use App\Models\Language;
use App\Models\PostVariant;
use Tests\TestCase;

class PostVariantTsLanguageTest extends TestCase
{

    public function testHandle() : void
    {
        $en = Language::factory()->create(['code' => 'en']);
        $fr = Language::factory()->create(['code' => 'fr']);

        $enVariants = PostVariant::factory()
            ->count(3)
            ->create(['language_id' => $en->id]);

        $frVariants = PostVariant::factory()
            ->count(3)
            ->create(['language_id' => $fr->id]);

        $this->artisan('migrate:post-variant-ts-language')
            ->assertExitCode(0);

        $enVariants->each(function ($variant) use ($en) {
            $this->assertEquals('english', $variant->refresh()->ts_language);
        });

        $frVariants->each(function ($variant) use ($fr) {
            $this->assertEquals('french', $variant->refresh()->ts_language);
        });
    }

}
