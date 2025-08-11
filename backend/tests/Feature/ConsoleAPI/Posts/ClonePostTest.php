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
        $post = PostFactory::oneFor($blog);

        $response = $this->consoleApi($blog, 'POST', 'post/' . $post->id .  '/clone');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('variants', $data);
        $variants = $data['variants'];
        $this->assertIsArray($variants);
        $this->assertCount(1, $variants);

        $post = Post::where('id', $data['id'])->first();
        $this->assertNotNull($post);

        $postVariant = $post->variants->first();
        $this->assertNotNull($postVariant);
    }
}
