<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;

it('matches media', function () {
    $fileName = 'test.svg';

    $file = UploadedFile::fake()->image($fileName);

    $blog = blogWithLanguage();
    $media = MediaRepository::upload($blog, $file);


    $pathMatcher = new PathMatcher($blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($file->getContent(), $responseObject->content);
    $this->assertEquals('image/svg+xml', $responseObject->mime_type);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::MEDIA);
});

it('gets mime type from the file name', function() {

    $url = 'https://image.com/image.svg';
    $blog = blogWithLanguage();

    Http::fake([
        'https://image.com/image.svg' => Http::response('test')
    ]);

    $media = (new MediaRepository)->uploadFromUrl($blog, $url);

    $pathMatcher = new PathMatcher($blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->mime_type)->toBe('image/svg+xml');

});

it('converts jpg to webp', function() {

    $file = UploadedFile::fake()->createWithContent(
        'test.jpg',
        file_get_contents(__DIR__  . '/test.jpg')
    );

    $webP = (string) file_get_contents(__DIR__  . '/test.webp');

    $blog = blogWithLanguage();
    $media = MediaRepository::upload($blog, $file);

    $pathMatcher = new PathMatcher($blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($webP, $responseObject->content);
    $this->assertEquals('image/webp', $responseObject->mime_type);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::MEDIA);

});

it('converts png to webp', function() {

    $file = UploadedFile::fake()->createWithContent(
        'test.png',
        file_get_contents(__DIR__  . '/test.png')
    );

    $webP = (string) file_get_contents(__DIR__  . '/test.webp');

    $blog = blogWithLanguage();
    $media = MediaRepository::upload($blog, $file);

    $pathMatcher = new PathMatcher($blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($webP, $responseObject->content);
    $this->assertEquals('image/webp', $responseObject->mime_type);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::MEDIA);

});