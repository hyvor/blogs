<?php declare(strict_types=1);

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Helpers\MimeTypes;

it('matches styles.css', function () {
    $file = 'index.scss';
    $content = 'body {color: red;}';

    $blog = blog();

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

    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
});

it('works with imports', function() {

    $blog = blog();

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
    expect($responseObject->content)->toBe('body {
  color: red;
}
');

});


it('shows an error when scss is wrong', function() {

    $file = 'index.scss';
    $content = '{';

    $blog = blog();

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

});