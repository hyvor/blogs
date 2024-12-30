<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagCreatedEvent;
use App\Models\Tag;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a tag with variant', function () {
    Event::fake();

    $name = 'Blogging';

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    consoleApi($blog, 'POST', '/tag', [
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('variants.0.name', $name)
                ->etc()
        );


    $tags = Tag::where('blog_id', $blog->id)->get();

    expect($tags->count())->toBe(1);
    expect($tags->first()->is_private)->toBe(false);
    expect($tags->first()->variants->count())->toBe(1);

    Event::assertDispatched(TagCreatedEvent::class);
});

it('creates a private tag', function() {

    Event::fake();

    $name = 'Blogging';

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    consoleApi($blog, 'POST', '/tag', [
        'name' => $name,
        'is_private' => true,
    ])
        ->assertOk();

    $tags = Tag::where('blog_id', $blog->id)->get();

    expect($tags->count())->toBe(1);
    expect($tags->first()->is_private)->toBe(true);

    Event::assertDispatched(TagCreatedEvent::class);

});