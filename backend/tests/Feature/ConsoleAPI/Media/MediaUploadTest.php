<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Media\Events\MediaCreatedEvent;
use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use League\Flysystem\Filesystem;

beforeEach(function () {
    $this->blog = blogWithAccess();
    
    $s3Service = new S3StorageService();
    $this->filesystem = $s3Service->getFilesystem(S3ConnectionDto::fromDefaultStorage());
    $this->prefix = 'blog/' . $this->blog->id . '/';
    
    // Clean up any existing files from previous test runs
    $filesToClean = ['test.png', 'new-name.png', 'new-name-1.png'];
    foreach ($filesToClean as $file) {
        try {
            $this->filesystem->delete($this->prefix . $file);
        } catch (\Exception $e) {
        }
    }
});

afterEach(function () {
    // Clean up after test
    $filesToClean = ['test.png', 'new-name.png', 'new-name-1.png'];
    foreach ($filesToClean as $file) {
        try {
            $this->filesystem->delete($this->prefix . $file);
        } catch (\Exception $e) {
        }
    }
});

it('uploads', function () {
    Event::fake();

    $blog = blogWithAccess();
    $file = UploadedFile::fake()->image('image.png')->size(100);

    BillingFake::enable(license: new BlogsLicense(storage: 1000));

    consoleApi($blog, 'POST', '/media', [
        'file' => $file,
        'name' => 'image.png'
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) => $json
                ->has('id')
                ->etc()
        );

    Event::assertDispatched(MediaCreatedEvent::class);
    expect($this->filesystem->fileExists('blog/' . $blog->id . '/image.png'))->toBeTrue();
});

it('uploads with duplicate name', function () {
    $blog = blogWithAccess();
    $prefix = 'blog/' . $blog->id . '/';
    $this->filesystem->write($prefix . 'image.png', 'content');

    $file = UploadedFile::fake()->image('image.png')->size(100);
    BillingFake::enable(license: new BlogsLicense(storage: 1000));

    $media = consoleApi($blog, 'POST', '/media', [
        'file' => $file,
        'name' => 'image.png'
    ])
        ->assertOk()
        ->json();

    expect($media['name'])->toBe('image-1.png');
    expect($this->filesystem->fileExists($prefix . 'image.png'))->toBeTrue();
});

it('converts to kebab case', function () {
    $blog = blogWithAccess();
    $file = UploadedFile::fake()->image('image.png')->size(100);
    BillingFake::enable(license: new BlogsLicense(storage: 1000));

    $media = consoleApi($blog, 'POST', '/media', [
        'file' => $file,
        'name' => 'My Imagé.png'
    ])
        ->assertOk()
        ->json();

    expect($media['name'])->toBe('my-imagé.png');
});

it('uploads with post ID', function () {
    $blog = blogWithAccess();
    BillingFake::enable(license: new BlogsLicense(storage: 1000));
    $response = consoleApi($blog, 'POST', '/media', [
        'file' => UploadedFile::fake()->image('image.png')->size(100),
        'post_id' => 2
    ])
        ->assertOk()
        ->assertJson(fn(AssertableJson $json) => $json->where('post_id', 2)->etc());
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

it('throws error when media size exceeded', function () {
    $blog = blogWithAccess();
    $blog->setCount('media', 10 ** 9 * 2);
    BillingFake::enable(license: new BlogsLicense(storage: 1000));

    consoleApi($blog, 'POST', '/media', [
        'file' => UploadedFile::fake()->image('image.png')->size(100),
    ])
        ->assertUnprocessable()
        ->assertSee('Total storage limit exceeded. Please upgrade your plan.');
});
