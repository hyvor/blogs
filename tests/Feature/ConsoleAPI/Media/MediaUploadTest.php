<?php

namespace Tests\Feature\ConsoleAPI\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;

it('uploads', function () {
    $file = UploadedFile::fake()->image('image.png')->size(100);

    $this->callConsoleApi('POST', '/media', [
        'file' => $file,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('id')->etc());
});

it('limits file size', function () {
    $file = UploadedFile::fake()->image('image.png')->size(config('limits.max_media_upload_size_kb') + 1);

    $this->callConsoleApi('POST', '/media', [
        'file' => $file,
    ])
        ->assertUnprocessable()
        ->assertSee(['must', 'not', 'kilobytes']);
});
