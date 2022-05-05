<?php

namespace Tests\Unit\Delivery;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\RedirectTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Media\MediaRepository;
use App\Domains\Redirect\RedirectRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Helpers\MimeTypes;
use App\Models\Blog;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->blog = Blog::find(config('test.blog_id'));
});

it('matches redirect', function () {
    $from = '/redirect';
    $to = 'https://somewhere.com';

    RedirectRepository::createRedirect(
        $this->blog->id,
        $from,
        $to,
        RedirectTypeEnum::PERMANENT
    );

    $pathMatcher = new PathMatcher($this->blog, $from);
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::REDIRECT, $responseObject->type);
    $this->assertEquals($to, $responseObject->to);
    $this->assertEquals(RedirectTypeEnum::PERMANENT->value, $responseObject->status);
});

it('matches assets', function () {
    $file = 'script.js';
    $content = 'var x = null';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::ASSETS,
        $file,
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, "/assets/$file");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);
});

// TODO: IT MATCHES PREVIEW PAGES

it('matches styles.css', function () {
    $file = 'index.scss';
    $content = 'body {color: red;}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::STYLES,
        $file,
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, "/styles.css");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    // SCSS processing alters the format, so do not test this
    // $this->assertEquals($content, $responseObject->content);
    $this->assertEquals(MimeTypes::getMimeFromExtension('css'), $responseObject->mime_type);
});

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
});
