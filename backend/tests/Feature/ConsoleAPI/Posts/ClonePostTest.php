<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Models\Post;
use Database\Factories\BlogFactory;
use Database\Factories\PostFactory;
use Database\Factories\PostVariantFactory;
use Tests\Case\DatabaseTestCase;

class ClonePostTest extends DatabaseTestCase
{
    public function test_post_cloning(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();
        $originalPost = PostFactory::oneFor($blog, [
            'is_page' => true,
            'is_featured' => true,
            'featured_image_url' => 'https://example.com/image.jpg',
            'canonical_url' => 'https://example.com/canonical',
            'code_head' => '<meta name="test" content="head">',
            'code_foot' => '<script>console.log("foot")</script>',
            'published_at' => now(),
        ], [
            'title' => 'Original Title',
            'description' => 'Original Description',
            'content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Original content"}]}]}',
            'content_unsaved' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Unsaved content"}]}]}',
            'seo_primary_keyword' => 'primary keyword',
            'seo_secondary_keywords' => ['keyword1', 'keyword2'],
            'link_analysis' => ['links' => ['https://example.com']],
        ]);
        $originalVariant = $originalPost->variants->first();

        $response = $this->consoleApi($blog, 'POST', 'post/' . $originalPost->id .  '/clone');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('variants', $data);
        $variants = $data['variants'];
        $this->assertIsArray($variants);
        $this->assertCount(1, $variants);

        $clonedPost = Post::where('id', $data['id'])->first();
        $this->assertNotNull($clonedPost);

        $this->assertEquals($originalPost->is_page, $clonedPost->is_page);
        $this->assertEquals($originalPost->featured_image_url, $clonedPost->featured_image_url);
        $this->assertEquals($originalPost->canonical_url, $clonedPost->canonical_url);
        $this->assertEquals($originalPost->code_head, $clonedPost->code_head);
        $this->assertEquals($originalPost->code_foot, $clonedPost->code_foot);

        $this->assertFalse($clonedPost->is_featured);
        $this->assertNull($clonedPost->published_at);

        $clonedVariant = $clonedPost->variants->first();
        $this->assertNotNull($clonedVariant);

        $this->assertEquals($originalVariant->title, $clonedVariant->title);
        $this->assertEquals($originalVariant->description, $clonedVariant->description);
        $this->assertEquals($originalVariant->content, $clonedVariant->content);
        $this->assertEquals($originalVariant->content_unsaved, $clonedVariant->content_unsaved);
        $this->assertEquals($originalVariant->seo_primary_keyword, $clonedVariant->seo_primary_keyword);
        $this->assertEquals($originalVariant->seo_secondary_keywords, $clonedVariant->seo_secondary_keywords);
        $this->assertEquals($originalVariant->link_analysis, $clonedVariant->link_analysis);

        $this->assertEquals('draft', $clonedVariant->status->value);
        $this->assertNull($clonedVariant->slug);
    }
}
