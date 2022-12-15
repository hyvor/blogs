<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Media\Events\MediaCreatedEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('uploads', function () {
    Event::fake();

    $blog = blogWithAccess();
    $file = UploadedFile::fake()->image('image.png')->size(100);

    consoleApi($blog, 'POST', '/media', [
        'file' => $file,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('id')
            ->etc()
        );

    Event::assertDispatched(MediaCreatedEvent::class);
});

it('uploads with post ID', function() {

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/media', [
        'file' => UploadedFile::fake()->image('image.png')->size(100),
        'post_id' => 2
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('post_id', 2)->etc());

});

it('limits file size', function () {
    $file = UploadedFile::fake()->image('image.png')->size(config('limits.max_media_upload_size_kb') + 1);

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/media', [
        'file' => $file,
    ])
        ->assertUnprocessable()
        ->assertSee(['must', 'not', 'kilobytes']);
});
