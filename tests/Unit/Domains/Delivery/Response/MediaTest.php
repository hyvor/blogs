<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

it('matches media', function () {
    $fileName = 'test.jpg';

    $file = UploadedFile::fake()->image($fileName);

    $media = MediaRepository::upload($this->blog, $file);

    $pathMatcher = new PathMatcher($this->blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($file->getContent(), $responseObject->content);
    $this->assertEquals('image/jpeg', $responseObject->mime_type);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::MEDIA);
});

it('gets mime type from the file name', function() {

    $url = 'https://image.com/image.svg';

    Http::fake([
        'https://image.com/image.svg' => Http::response('test')
    ]);

    $media = (new MediaRepository)->uploadFromUrl($this->blog, $url);

    $pathMatcher = new PathMatcher($this->blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->mime_type)->toBe('image/svg+xml');

});