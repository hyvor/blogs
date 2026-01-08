<?php

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Route\RouteRepository;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _meta on index', function () {

    $content = '{{ _meta.title }}';
    $blog = blogWithLanguageAndRoutes();
    $variants = $blog->variants;

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe(VariantsHelper::getVariantValue('name', $variants, $blog->languages[0]));
});


it('no errors on _meta for custom routes', function () {

    $content = '_meta goes here:{% if _meta is defined %}_meta was defined{% endif %}';
    $blog = blogWithLanguageAndRoutes();
    $variants = $blog->variants;

    RouteRepository::createRoute(
        $blog,
        'test',
        '/test',
        'test',
    );

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'test.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/test');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('_meta goes here:');
});
