<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagVariantDeletedEvent;
use Illuminate\Support\Facades\Event;

it('deletes a tag variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addLanguage($blog);
    $tag = addTag($blog);

    $variants = $tag->variants()->count();

    consoleApi($blog, 'DELETE', "/tag/$tag->id/variant", [
            'language_id' => $blog->languages[1]->id,
        ])
        ->assertOk();

    expect($tag->variants()->count())->toBe($variants - 1);

    Event::assertDispatched(TagVariantDeletedEvent::class);
});

it('does not delete primary language variant', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $tag = addTag($blog);

    consoleApi($blog, 'DELETE', "/tag/$tag->id/variant", [
            'language_id' => $blog->languages[0]->id,
        ])
        ->assertUnprocessable();
});
