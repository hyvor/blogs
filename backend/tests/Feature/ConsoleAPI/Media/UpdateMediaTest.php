<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Blog\Jobs\UpdateMediaUrlsInPostsJob;
use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use App\Models\Blog;
use App\Models\Media;
use Illuminate\Support\Facades\Queue;
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

it('updates name and moves file',function() {

    Queue::fake();
    
    $this->filesystem->write($this->prefix . 'test.png', 'content');

    $media = Media::factory()->create([
        'blog_id' => $this->blog->id,
        'name' => 'test.png',
    ]);

    consoleApi($this->blog, 'PATCH', '/media/' . $media->id, [
        'name' => 'new-name.png'
    ])
        ->assertOk()
        ->assertJsonPath('name', 'new-name.png');

    expect($media->refresh()->name)->toBe('new-name.png');

    expect($this->filesystem->fileExists($this->prefix . 'test.png'))->toBeFalse();
    expect($this->filesystem->fileExists($this->prefix . 'new-name.png'))->toBeTrue();

    Queue::assertPushed(UpdateMediaUrlsInPostsJob::class, function (UpdateMediaUrlsInPostsJob $job) {
        expect($job->oldUrl)->toEndWith('/media/test.png');
        expect($job->newUrl)->toEndWith('/media/new-name.png');
        return true;
    });

});

it('updates to kebab case', function() {

    Queue::fake();
    
    $this->filesystem->write($this->prefix . 'test.png', 'content');

    $media = Media::factory()->create([
        'blog_id' => $this->blog->id,
        'name' => 'test.png',
    ]);

    consoleApi($this->blog, 'PATCH', '/media/' . $media->id, [
        'name' => 'New Name.png'
    ])
        ->assertOk()
        ->assertJsonPath('name', 'new-name.png');

    expect($media->refresh()->name)->toBe('new-name.png');

    expect($this->filesystem->fileExists($this->prefix . 'test.png'))->toBeFalse();
    expect($this->filesystem->fileExists($this->prefix . 'new-name.png'))->toBeTrue();

    Queue::assertPushed(UpdateMediaUrlsInPostsJob::class, function (UpdateMediaUrlsInPostsJob $job) {
        expect($job->oldUrl)->toEndWith('/media/test.png');
        expect($job->newUrl)->toEndWith('/media/new-name.png');
        return true;
    });

});

it('handles duplicates', function() {

    $this->filesystem->write($this->prefix . 'test.png', 'content');
    $this->filesystem->write($this->prefix . 'new-name.png', 'content');

    $media = Media::factory()->create([
        'blog_id' => $this->blog->id,
        'name' => 'test.png',
    ]);

    consoleApi($this->blog, 'PATCH', '/media/' . $media->id, [
        'name' => 'new-name.png'
    ])
        ->assertOk()
        ->assertJsonPath('name', 'new-name-1.png');

    expect($media->refresh()->name)->toBe('new-name-1.png');

    expect($this->filesystem->fileExists($this->prefix . 'test.png'))->toBeFalse();
    expect($this->filesystem->fileExists($this->prefix . 'new-name.png'))->toBeTrue();
    expect($this->filesystem->fileExists($this->prefix . 'new-name-1.png'))->toBeTrue();

});
