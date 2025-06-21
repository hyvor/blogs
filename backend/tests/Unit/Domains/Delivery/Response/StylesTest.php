<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Helpers\MimeTypes;
use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class StylesTest extends DatabaseTestCase
{

    public function testMatchesStylesCss(): void
    {
        $file = 'index.scss';
        $content = 'body {color: red;}';

        $blog = BlogFactory::one();

        ThemeFilesRepository::createOrUpdateFile(
            $blog,
            ThemeFileFolderEnum::STYLES,
            $file,
            $content,
        );

        $pathMatcher = new PathMatcher($blog, '/styles.css');
        $responseObject = $pathMatcher->getResponseObject();

        $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
        // SCSS processing alters the format, so do not test this
        // $this->assertEquals($content, $responseObject->content);
        $this->assertEquals(MimeTypes::getMimeFromExtension('css'), $responseObject->mime_type);
        $this->assertEquals(DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR, $responseObject->cache_control);

        expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
    }

    public function testWorksWithImports(): void
    {
        $blog = BlogFactory::one();

        ThemeFilesRepository::createOrUpdateFile(
            $blog,
            ThemeFileFolderEnum::STYLES,
            'index.scss',
            '@import "imported.scss";',
        );

        ThemeFilesRepository::createOrUpdateFile(
            $blog,
            ThemeFileFolderEnum::STYLES,
            'imported.scss',
            'body {color: red;}',
        );

        $pathMatcher = new PathMatcher($blog, '/styles.css');

        $responseObject = $pathMatcher->getResponseObject();

        expect($responseObject->status)->toBe(200);
        expect($responseObject->content)->toBe(
            'body{color:red}'
        );
    }


    public function testShowsErrorOnInvalidScss(): void
    {
        $file = 'index.scss';
        $content = '{';

        $blog = BlogFactory::one();

        ThemeFilesRepository::createOrUpdateFile(
            $blog,
            ThemeFileFolderEnum::STYLES,
            $file,
            $content,
        );

        $pathMatcher = new PathMatcher($blog, '/styles.css');
        $responseObject = $pathMatcher->getResponseObject();

        expect($responseObject->status)->toBe(500);
        expect($responseObject->content)->toContain('SCSS Error');
    }

    public function testNoCacheForDev(): void
    {
        $file = 'index.scss';
        $content = 'body {color: red;}';

        $blog = BlogFactory::one(['type' => 'dev']);

        ThemeFilesRepository::createOrUpdateFile(
            $blog,
            ThemeFileFolderEnum::STYLES,
            $file,
            $content,
        );

        $pathMatcher = new PathMatcher($blog, '/styles.css');
        $responseObject = $pathMatcher->getResponseObject();
        $this->assertEquals(DeliveryAPICacheControlHeaderEnum::NO_CACHE, $responseObject->cache_control);
    }

}