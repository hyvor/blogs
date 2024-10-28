<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Media\Events\MediaCreatedEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;

it('uploads', function () {
    Event::fake();
    Storage::fake();

    $blog = blogWithAccess();
    $file = UploadedFile::fake()->image('image.png')->size(100);

    consoleApi($blog, 'POST', '/media', [
        'file' => $file,
        'file_name' => 'image.png'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('id')
            ->etc()
        );

    Event::assertDispatched(MediaCreatedEvent::class);
    Storage::has('blog/' . $blog->id . '/image.png');
});

it('uploads with duplicate name', function() {

    Storage::fake();
    $blog = blogWithAccess();
    Storage::put('blog/' . $blog->id . '/image.png', 'content');

    $file = UploadedFile::fake()->image('image.png')->size(100);

    $media = consoleApi($blog, 'POST', '/media', [
        'file' => $file,
        'file_name' => 'image.png'
    ])
        ->assertOk()
        ->json();

    expect($media['name'])->toMatch('/image-\w+\.png/');

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

it('throws error when media size exceeded', function() {

    $blog = blogWithAccess();
    $blog->setCount('media', 10**9*2);

    consoleApi($blog, 'POST', '/media', [
        'file' => UploadedFile::fake()->image('image.png')->size(100),
    ])
        ->assertUnprocessable()
        ->assertSee('Total storage limit exceeded. Please upgrade your plan.');

});