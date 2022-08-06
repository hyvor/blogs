<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Events\BlogVariantUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->language = blog()->languages[0];
});

it('validates', function () {
    $this->callConsoleApi('PATCH', '/blog/variant')->assertUnprocessable();
    $this->callConsoleApi(
        'PATCH',
        '/blog/variant',
        ['language_id' => 2, 'name' => false]
    )->assertUnprocessable();
});

it('updates name and emits event', function () {

    Event::fake();

    $name = 'Name';

    $this->callConsoleApi(
        'PATCH',
        '/blog/variant',
        [
            'language_id' => $this->language->id,
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

    $this->callConsoleApi(
        'PATCH',
        '/blog/variant',
        [
            'language_id' => $this->language->id,
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
