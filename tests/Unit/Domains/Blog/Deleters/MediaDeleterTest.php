<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\MediaDeleter;
use App\Domains\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('deletes media', function() {

    Storage::fake();

    $blog = blog();
    $blog2 = newBlog();

    // upload a few
    $media1 = MediaRepository::upload($blog, UploadedFile::fake()->image('photo1.jpg'));
    $media2 = MediaRepository::upload($blog, UploadedFile::fake()->image('photo2.jpg'));
    $mediaOtherBlog = MediaRepository::upload($blog2, UploadedFile::fake()->image('photo2.jpg'));

    $media1Path = "blog/$blog->id/$media1->name";
    $media2Path = "blog/$blog->id/$media2->name";
    $mediaOtherBlogPath = "blog/$blog2->id/$mediaOtherBlog->name";

    Storage::assertExists($media1Path);
    Storage::assertExists($media2Path);
    Storage::assertExists($mediaOtherBlogPath);

    expect($blog->medias()->count())->toBe(2);

    (new MediaDeleter($blog))->delete();

    expect($blog->medias()->count())->toBe(0);

    Storage::assertMissing($media1Path);
    Storage::assertMissing($media2Path);
    Storage::assertExists($mediaOtherBlogPath);

});