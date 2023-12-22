<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Domains\Tag\Events\TagDeletedEvent;
use App\Domains\Tag\Events\TagVariantDeletedEvent;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\TagVariant;
use Illuminate\Support\Facades\Event;

it('deletes the tag, its variants, and post tags', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addLanguage($blog);
    addLanguage($blog);

    $tag = addTag($blog);
    addTagToPost(addPost($blog), $tag);

    // has
    expect(Tag::find($tag->id))->toBeInstanceOf(Tag::class);
    expect(TagVariant::where('tag_id', $tag->id)->count())->toBe(3);
    expect(PostTag::where('tag_id', $tag->id)->count())->toBe(1);

    consoleApi($blog, 'DELETE', "/tag/$tag->id")
        ->assertOk();

    // nope
    expect(Tag::find($tag->id))->toBeNull();
    expect(TagVariant::where('tag_id', $tag->id)->count())->toBe(0);
    expect(PostTag::where('tag_id', $tag->id)->count())->toBe(0);

    Event::assertDispatched(TagDeletedEvent::class);
    Event::assertDispatched(TagVariantDeletedEvent::class, 3);
});
