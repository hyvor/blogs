<?php

namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Models\LinkAnalyzerLink;
use Database\Factories\BlogFactory;
use Database\Factories\PostFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Case\DatabaseTestCase;

class IgnoreLinkTest extends DatabaseTestCase
{

    #[TestWith([true])]
    #[TestWith([false])]
    public function testIgnoresLink(bool $current): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();
        $post = PostFactory::publishedFor($blog);
        $postVariant = $post->variants[0];
        $this->assertNotNull($postVariant);

        $url = 'https://hyvor.com';

        $link = LinkAnalyzerLink::factory()->create([
            'blog_id' => $blog->id,
            'post_variant_id' => $postVariant->id,
            'url' => $url,
            'ignore' => $current
        ]);

        $this->consoleApi($blog, 'PATCH', '/link-analysis/ignore-link', [
            'post_variant_id' => $postVariant->id,
            'url' => $url,
            'status' => !$current
        ])
            ->assertOk();

        $link->refresh();
        $this->assertSame(!$current, $link->ignore);

        $this->assertSame([
            $url => $current ? $link->status_code : -2
        ], $postVariant->refresh()->link_analysis);
    }


    public function testIgnoresInternalLink(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();
        $post = PostFactory::publishedFor($blog);
        $postVariant = $post->variants[0];
        $this->assertNotNull($postVariant);

        $link = LinkAnalyzerLink::factory()->create([
            'blog_id' => $blog->id,
            'post_variant_id' => $postVariant->id,
            'url' => '/welcome',
        ]);

        $this->consoleApi($blog, 'PATCH', '/link-analysis/ignore-link', [
            'post_variant_id' => $postVariant->id,
            'url' => '/welcome',
            'status' => true
        ])
            ->assertOk();

        $link->refresh();
        $this->assertNotNull($link);
        $this->assertTrue($link->ignore);

        $this->assertSame([
            '/welcome' => -2
        ], $postVariant->refresh()->link_analysis);
    }

}