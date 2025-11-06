<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\MediaDeleter;
use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use App\Domains\Media\MediaRepository;
use Illuminate\Http\UploadedFile;

it('deletes media', function () {
    $blog = blog();
    $blog2 = blog();

    $media1 = MediaRepository::upload($blog, UploadedFile::fake()->image('photo1.jpg'));
    $media2 = MediaRepository::upload($blog, UploadedFile::fake()->image('photo2.jpg'));
    $mediaOtherBlog = MediaRepository::upload($blog2, UploadedFile::fake()->image('photo2.jpg'));

    $media1Path = "blog/$blog->id/$media1->name";
    $media2Path = "blog/$blog->id/$media2->name";
    $mediaOtherBlogPath = "blog/$blog2->id/$mediaOtherBlog->name";

    $s3Service = new S3StorageService();
    $filesystem = $s3Service->getFilesystem(S3ConnectionDto::fromDefaultStorage());

    expect($filesystem->fileExists($media1Path))->toBeTrue();
    expect($filesystem->fileExists($media2Path))->toBeTrue();
    expect($filesystem->fileExists($mediaOtherBlogPath))->toBeTrue();

    expect($blog->medias()->count())->toBe(2);

    (new MediaDeleter($blog))->delete();

    expect($blog->medias()->count())->toBe(0);

    expect($filesystem->fileExists($media1Path))->toBeFalse();
    expect($filesystem->fileExists($media2Path))->toBeFalse();
    expect($filesystem->fileExists($mediaOtherBlogPath))->toBeTrue();
});
