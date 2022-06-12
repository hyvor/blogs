<?php
namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _foot in index', function() {

    $blog = blog();
    $variant = $blog->variants[0];

    $codeFoot = 'This is code head {{ _blog.name }}';
    $codeFootRendered = "This is code head $variant->name";

    $blog->setMeta([
        'code_foot' => $codeFoot,
    ]);

    $content = '{{ _foot | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain('flashload.js');
    expect($content)->toContain($codeFootRendered);

});

it('sets _head in a post page', function() {

    $blog = blog();
    $post = aPublishedPost();

    $postCodeFoot = 'A post code head {{ _post.id }}';
    $postCodeFootRendered = "A post code head $post->id";
    $post->update(['code_foot' => $postCodeFoot]);

    $content = '{{ _foot | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/$post->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain($postCodeFootRendered);

});