<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Route\PermalinkRepository;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Illuminate\Support\Facades\Http;

it('uploads from url', function () {
    $blog = blogWithAccess();
    $blogUrl = PermalinkRepository::getBaseUrl($blog);
    $url = 'https://example.com/image.txt';

    $license = BlogsLicense::trial();
    $license->storage = 1000;
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);

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

it('rejects uploading larger files', function () {
    $blog = blogWithAccess();
    $url = 'https://example.com/image.txt';

    $license = BlogsLicense::trial();
    $license->storage = 1000;
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);
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


it('throws error when media size exceeded', function () {
    $blog = blogWithAccess();
    $blog->setCount('media', 10 ** 9 * 2);

    $license = BlogsLicense::trial();
    $license->storage = 1000;
    BillingFake::enable([$blog->organization_id => new ResolvedLicense(ResolvedLicenseType::TRIAL, $license)]);

    consoleApi($blog, 'POST', '/media/from-url', [
        'url' => 'https://test.com'
    ])
        ->assertUnprocessable()
        ->assertSee('Total storage limit exceeded. Please upgrade your plan.');
});
