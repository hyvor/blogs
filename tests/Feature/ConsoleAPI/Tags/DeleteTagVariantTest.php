<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagVariantDeletedEvent;
use Illuminate\Support\Facades\Event;

it('deletes a tag variant', function () {

    Event::fake();

    $tag = $this->blog->tags()->first();

    $variants = $tag->variants()->count();

    $this
        ->callConsoleApi('DELETE', "/tag/$tag->id/variant", [
            'language_id' => $this->blog->languages[1]->id,
        ])
        ->assertOk();

    expect($tag->variants()->count())->toBe($variants - 1);

    Event::assertDispatched(TagVariantDeletedEvent::class);

});



it('does not delete primary language variant', function () {
    $tag = $this->blog->users()->first();

    $this
        ->callConsoleApi('DELETE', "/tag/$tag->id/variant", [
            'language_id' => $this->blog->languages[0]->id,
        ])
        ->assertUnprocessable();
});