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
    $language = addPrimaryLanguage($blog, attr: ['code' => 'en']);
    $language2 = addLanguage($blog, attr: ['code' => 'es']);

    $this->assertEquals(0, $post->variants()->count());


    consoleApi($blog, 'POST', "/post/$post->id/variant", [
        'language_id' => $language->id,
    ])
        ->assertOk()
        ->assertJson(fn(AssertableJson $json) => $json->has('status')->etc());

    consoleApi($blog, 'POST', "/post/$post->id/variant", [
        'language_id' => $language2->id,
    ])
        ->assertOk()
        ->assertJson(fn(AssertableJson $json) => $json->has('status')->etc());

    $this->assertEquals(2, $post->variants()->count());

    $variants = $post->variants;

    $firstVariant = $variants->first();
    expect($firstVariant->language_id)->toBe($language->id);
    expect($firstVariant->ts_language)->toBe('english');

    $secondVariant = $variants->last();
    expect($secondVariant->language_id)->toBe($language2->id);
    expect($secondVariant->ts_language)->toBe('spanish');

    Event::assertDispatched(PostVariantCreatedEvent::class);
});
