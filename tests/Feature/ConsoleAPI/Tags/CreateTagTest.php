<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a tag with variant', function () {
    Event::fake();

    $name = 'Blogging';

    $this->callConsoleApi('POST', '/tag', [
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('variants.0.name', $name)
                ->etc()
        );

    Event::assertDispatched(TagCreatedEvent::class);
});
