<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagVariantCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a user variant', function () {
    Event::fake();

    $language = $this->blog->languages[0];
    $language2 = $this->blog->languages[1];
    $tag = $this->blog->tags()->first();

    $tag->variants()->delete();

    $this->assertEquals(0, $tag->variants()->count());

    $this
        ->callConsoleApi('POST', "/tag/$tag->id/variant", [
            'language_id' => $language->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('name')->etc());


    $this
        ->callConsoleApi('POST', "/tag/$tag->id/variant", [
            'language_id' => $language2->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('name')->etc());

    $this->assertEquals(2, $tag->variants()->count());

    Event::assertDispatched(TagVariantCreatedEvent::class);
});
