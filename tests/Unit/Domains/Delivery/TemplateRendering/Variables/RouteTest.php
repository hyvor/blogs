<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\RouteRepository;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _route variable', function () {
    $routeName = 'test';
    $template = 'test_template';

    RouteRepository::createRoute(
        $this->blog,
        $routeName,
        '/test/{slug}',
        $template,
        'id=1',
        'text/xml',
    );

    $content = <<<TWIG
    {{ _route.name }}
    {{ _route.template }}
    {{ _route.params.slug }}
    {{ _route.posts_filter }}
    {{ _route.content_type }}
    TWIG;

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'test_template.twig',
        $content,
    );

    $rendered = "$routeName\n$template\nhi\nid=1\ntext/xml";


    $pathMatcher = new PathMatcher($this->blog, '/test/hi');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe($rendered);
});
