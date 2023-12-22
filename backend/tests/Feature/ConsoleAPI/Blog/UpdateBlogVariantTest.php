<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('validates', function () {
    $blog = blogWithAccess();
    consoleApi($blog, 'PATCH', '/blog/variant')->assertUnprocessable();
    consoleApi($blog,
        'PATCH',
        '/blog/variant',
        ['language_id' => 2, 'name' => false]
    )->assertUnprocessable();
});

it('updates name and emits event', function () {
    Event::fake();

    $name = 'Name';

    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);
    addBlogVariants($blog, $language);

    consoleApi($blog,
        'PATCH',
        '/blog/variant',
        [
            'language_id' => $language->id,
            'name' => $name,
        ]
    )
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('name', $name)
                ->etc()
        );

    Event::assertDispatched(BlogVariantUpdatedEvent::class);
});

it('updates description', function () {
    $description = 'Hello world';

    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);
    addBlogVariants($blog, $language);

    consoleApi($blog,
        'PATCH',
        '/blog/variant',
        [
            'language_id' => $language->id,
            'description' => $description,
        ]
    )
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('description', $description)
                ->etc()
        );
});
