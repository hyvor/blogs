<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Media\Events\MediaCreatedEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('uploads', function () {
    Event::fake();

    $file = UploadedFile::fake()->image('image.png')->size(100);

    $this->callConsoleApi('POST', '/media', [
        'file' => $file,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('id')->etc());

    Event::assertDispatched(MediaCreatedEvent::class);
});

it('limits file size', function () {
    $file = UploadedFile::fake()->image('image.png')->size(config('limits.max_media_upload_size_kb') + 1);

    $this->callConsoleApi('POST', '/media', [
        'file' => $file,
    ])
        ->assertUnprocessable()
        ->assertSee(['must', 'not', 'kilobytes']);
});
