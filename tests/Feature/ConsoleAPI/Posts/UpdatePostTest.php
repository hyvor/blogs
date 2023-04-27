<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a post', function () {
    Event::fake();

    $blog = blogWithAccess();
    $post = addPost($blog);

    // $slug = 'hello-world';
    $isFeatured = true;
    $canonicalUrl = 'https://example.com';
    $featuredImageUrl = 'https://example.com/image.png';
    $codeHead = 'head code';
    $codeFoot = 'foot code';

    consoleApi($blog, 'PATCH', "/post/$post->id", [
            //'slug' => $slug,
            'is_featured' => $isFeatured,
            'canonical_url' => $canonicalUrl,
            'featured_image_url' => $featuredImageUrl,
            'code_head' => $codeHead,
            'code_foot' => $codeFoot,
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                // ->where('slug', $slug)
                ->where('is_featured', $isFeatured)
                ->where('canonical_url', $canonicalUrl)
                ->where('featured_image_url', $featuredImageUrl)
                ->where('code_head', $codeHead)
                ->where('code_foot', $codeFoot)
                ->etc()
        );

    Event::assertDispatched(PostUpdatedEvent::class);
});