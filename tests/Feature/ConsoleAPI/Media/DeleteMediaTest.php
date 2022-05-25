<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Models\Blog;
use App\Models\Media;

it('deletes', function() {

    $media = Media::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('DELETE', "/media/$media->id")
        ->assertOk();

    expect(Media::find($media->id))->toBeNull();

});

it('cannot delete other blogs media', function() {

    $media = Media::factory()->create(['blog_id' => Blog::find(config('test.not_blog_id'))]);

    $this->callConsoleApi('DELETE', "/media/$media->id")
        ->assertForbidden();

    expect(Media::find($media->id)->id)->toBeInt();

});