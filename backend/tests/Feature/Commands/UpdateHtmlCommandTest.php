<?php

namespace Tests\Feature\Commands;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Tests\TestCase;

class UpdateHtmlCommandTest extends TestCase
{

    public function testHandle(): void
    {

        $blog = Blog::factory()->create();

        $post = Post::factory()->create([
            'blog_id' => $blog->id
        ]);
        $variant = PostVariant::factory()->create([
            'post_id' => $post->id,
            'content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello world"}]}]}',
            'content_html' => 'old content'
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('blog:update-html', ['subdomain' => $blog->subdomain]);
        $command->expectsOutput('Updating content html of all posts...')
            ->expectsOutput('Updated 1 posts. First id: 1, Last id: 1')
            ->assertExitCode(0);
        $command->run();

        $variant = $variant->refresh();
        $this->assertEquals('<p>Hello world</p>', $variant->content_html);

    }

}
