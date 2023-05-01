<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Route\PermalinkRepository;
use Illuminate\Support\Facades\Http;

it('uploads from url', function() {

    $blog = blogWithAccess();
    $blogUrl = PermalinkRepository::getBaseUrl($blog);
    $url = 'https://example.com/image.txt';

    Http::fake([
        $url => Http::response('test', 200, ['Content-Type' => 'text/plain']),
    ]);

    $media = consoleApi($blog, 'POST', '/media/from-url', [
        'url' => $url,
    ])
        ->assertOk()
        ->json();

    expect($media['id'])->toBeGreaterThan(0)
        ->and($media['url'])->toStartWith($blogUrl . '/media');

});

it('rejects uploading larger files', function() {

    $blog = blogWithAccess();
    $url = 'https://example.com/image.txt';

    config(['limits.max_media_upload_size_kb' => 1]);

    Http::fake([
        $url => Http::response(
            str_repeat('a', 1025),
            200,
            ['Content-Type' => 'text/plain']
        ),
    ]);

    consoleApi($blog, 'POST', '/media/from-url', [
        'url' => $url,
    ])
        ->assertUnprocessable()
        ->assertSee('File size is too large');

});
