<?php
namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Media\MediaRepository;
use App\Helpers\MimeTypes;
use Illuminate\Http\UploadedFile;

it('matches media', function () {
    $fileName = 'test.jpg';

    $file = UploadedFile::fake()->image($fileName);

    $media = MediaRepository::upload($this->blog, $file);

    $pathMatcher = new PathMatcher($this->blog, "/media/$media->name");
    $responseObject = $pathMatcher->getResponseObject();


    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($file->getContent(), $responseObject->content);
    $this->assertEquals(MimeTypes::getMimeFromExtension('jpg'), $responseObject->mime_type);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::MEDIA);
});
