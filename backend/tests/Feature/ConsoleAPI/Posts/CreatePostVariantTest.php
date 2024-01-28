<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostVariantCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a post variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addDefaultRoutes($blog);

    $post = addPost($blog);
    $language = addPrimaryLanguage($blog);
    $language2 = addLanguage($blog);

    $this->assertEquals(0, $post->variants()->count());


    consoleApi($blog, 'POST', "/post/$post->id/variant", [
            'language_id' => $language->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('status')->etc());

    consoleApi($blog, 'POST', "/post/$post->id/variant", [
            'language_id' => $language2->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('status')->etc());

    $this->assertEquals(2, $post->variants()->count());

    Event::assertDispatched(PostVariantCreatedEvent::class);
});
