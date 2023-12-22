<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Media\Events\MediaDeletedEvent;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Support\Facades\Event;

it('deletes', function () {
    Event::fake();

    $blog = blogWithAccess();
    $media = Media::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'DELETE', "/media/$media->id")
        ->assertOk();

    expect(Media::find($media->id))->toBeNull();

    Event::assertDispatched(MediaDeletedEvent::class);
});

it('cannot delete other blogs media', function () {

    $blog = blogWithAccess();
    $media = Media::factory()->create(['blog_id' => $blog]);

    consoleApi(blogWithAccess(), 'DELETE', "/media/$media->id")
        ->assertForbidden();

    expect(Media::find($media->id)->id)->toBeInt();
});
