<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Models\Tag;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a tag', function () {
    Event::fake();

    $tag = Tag::factory()->create([
        'blog_id' => blog(),
    ]);

    $slug = 'hello-world';
    $codeHead = 'var x = head';
    $codeFoot = 'var y = foot';

    $this->callConsoleApi('PATCH', "/tag/$tag->id", [
        'slug' => $slug,
        'code_head' => $codeHead,
        'code_foot' => $codeFoot,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('slug', $slug)
                ->where('code_head', $codeHead)
                ->where('code_foot', $codeFoot)
                ->etc()
        );

    Event::assertDispatched(TagUpdatedEvent::class);
});
