<?php

namespace Tests\Unit\Domains\Import\Medium;

use App\Domains\App\JobMessageLog;
use App\Domains\Import\Parser\MediumParser;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\PostContentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediumParserTest extends TestCase
{
    public function test_parses_medium_export(): void
    {
        Http::fake();

        $path = __DIR__ . '/example';
        $blog = blogWithLanguageAndRoutes();

        $messages = new JobMessageLog();
        $parser = new MediumParser(
            $blog,
            $path,
            $messages,
        );
        $parser->parse();

        $posts = $parser->posts;
        
        usort($posts, fn($a, $b) => $a->publishedAt <=> $b->publishedAt);

        $this->assertCount(2, $posts);

        // Example 1
        $post1 = $posts[0];
        $this->assertEquals('2025-12-01', $post1->publishedAt->toDateString());
        $this->assertEquals('https://example.com/image1.jpg', $post1->featuredImageUrl);
        
        $variant1 = $post1->variants[0];
        $this->assertEquals('Test Example 1', $variant1->title);
        $this->assertEquals('example-1-slug', $variant1->slug);
        $this->assertEquals('Description 1', $variant1->description);
        
        $contentDoc1 = PostContentService::getDocumentFromJson($variant1->content, $blog);
        $this->assertCount(0, $contentDoc1->getNodes(Image::class));
        
        $html1 = PostContentService::getHtml($variant1->content, $blog);
        $this->assertStringContainsString('Content 1', $html1);

        // Example 2
        $post2 = $posts[1];
        $this->assertEquals('2025-12-02', $post2->publishedAt->toDateString());
        $this->assertNull($post2->featuredImageUrl);

        $variant2 = $post2->variants[0];
        $this->assertEquals('Test Example 2', $variant2->title);
        $this->assertEquals('example-2-slug', $variant2->slug);
        $this->assertEquals('Description 2', $variant2->description);

        $contentDoc2 = PostContentService::getDocumentFromJson($variant2->content, $blog);
        $this->assertCount(0, $contentDoc2->getNodes(Image::class));
        
        $html2 = PostContentService::getHtml($variant2->content, $blog);
        $this->assertStringContainsString('Content 2', $html2);
    }
}
