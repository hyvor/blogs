<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagVariantUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates tag variant', function () {
    Event::fake();

    $tag = $this->blog->tags()->first();
    $variant = $tag->variants[0];
    $language = $variant->language;

    $name = 'Hey';
    $description = 'I am hey';

    $this
        ->callConsoleApi('PATCH', "/tag/$tag->id/variant", [
            'language_id' => $language->id,
            'name' => $name,
            'description' => $description,
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                    ->where('name', $name)
                    ->where('description', $description)
                    ->etc()
        );

    Event::assertDispatched(TagVariantUpdatedEvent::class);
});
