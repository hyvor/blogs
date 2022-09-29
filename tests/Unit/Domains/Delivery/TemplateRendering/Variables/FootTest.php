<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Subscription;

it('sets _foot in index', function () {
    $blog = blog();
    $variant = $blog->variants[0];

    $codeFoot = 'This is code head {{ _blog.name }}';
    $codeFootRendered = htmlspecialchars("This is code head $variant->name");

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

it('sets _foot in a post page', function () {
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

it('sets flashload basepath', function() {

    $blog = blog();
    addThemeTemplateFile('{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('basePath: ""');

});

it('sets flashload basepath to /blog', function() {

    $blog = blog();
    $blog->hosting_at = BlogHostingAtEnum::SELF;
    $blog->hosting_url = 'https://hyvor.com/blog';
    $blog->save();

    addThemeTemplateFile('{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('basePath: "blog"');

});

it('sets flashload basepath to /blog/page', function() {


    $blog = blog();
    $blog->hosting_at = BlogHostingAtEnum::SELF;
    $blog->hosting_url = 'https://hyvor.com/blog/page';
    $blog->save();

    addThemeTemplateFile('{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('basePath: "blog/page"');

});

it('adds powered by for free plan blogs', function() {

    $blog = blog();
    addThemeTemplateFile('{{ _foot | template }}');
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->toContain('Powered by Hyvor Blogs');

});

it('does not add powered by to non-free blogs', function() {

    $blog = blog();
    Subscription::factory()->create(['blog_id' => $blog]);
    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    expect($responseObject->content)->not->toContain('Powered by Hyvor Blogs');

});