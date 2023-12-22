<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagVariantCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a tag variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addDefaultRoutes($blog);

    $language = addPrimaryLanguage($blog);
    $language2 = addLanguage($blog);
    $tag = addTag($blog);

    $tag->variants()->delete();

    $this->assertEquals(0, $tag->variants()->count());

    consoleApi($blog, 'POST', "/tag/$tag->id/variant", [
            'language_id' => $language->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('name')->etc());

    consoleApi($blog, 'POST', "/tag/$tag->id/variant", [
            'language_id' => $language2->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('name')->etc());

    $this->assertEquals(2, $tag->variants()->count());

    Event::assertDispatched(TagVariantCreatedEvent::class);
});
