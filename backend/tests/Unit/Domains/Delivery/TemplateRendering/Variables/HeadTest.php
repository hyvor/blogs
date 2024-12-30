<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _head in index', function () {

    $blog = blogWithLanguageAndRoutes();
    $variant = $blog->variants[0];
    $variantName = htmlspecialchars($variant->name);
    $blogUrl = PermalinkRepository::getBlogPermalink($blog, $blog->languages[0]);

    $codeHead = 'This is code head {{ _blog.name }}';
    $codeHeadRendered = "This is code head $variantName";

    $twitterUrl = 'https://twitter.com/HyvorBlogs';

    $blog->setMeta([
        'code_head' => $codeHead,
        'social_twitter' => $twitterUrl,
    ]);

    $content = '{{ _head | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain('<meta name="generator" content="Hyvor Blogs" />');
    // styles.css
    expect($content)->toContain("<link rel=\"stylesheet\" href=\"$blogUrl/styles.css\" />");
    // code_head
    expect($content)->toContain($codeHeadRendered);
    // title
    expect($content)->toContain("<title>$variantName</title>");
    // description
    $variantDescription = htmlspecialchars($variant->description);
    expect($content)->toContain("<meta name=\"description\" content=\"$variantDescription\" />");
    // canonical
    expect($content)->toContain("<link rel=\"canonical\" href=\"$blogUrl\" />");
    // twitter
    expect($content)->toContain('<meta name="twitter:site" content="@HyvorBlogs" />');
});

it('sets _head in a post page', function () {
    $blog = blogWithLanguageAndRoutes();
    $post = addPublishedPost($blog);

    $postCodeHead = 'A post code head {{ _post.id }}';
    $postCodeHeadRendered = "A post code head $post->id";
    $post->update(['code_head' => $postCodeHead]);

    $user = addUser($blog);
    addAuthorToPost($post, $user);

    $tag = addTag($blog);
    addTagToPost($post, $tag);

    $variant = $post->variants[0];

    $publishedAt = $post->published_at->toIso8601String();
    $updatedAt = $variant->updated_at->toIso8601String();

    $content = '{{ _head | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    // add twitter URL to first author
    $post->authors[0]->update(['social_twitter' => 'https://twitter.com/Author']);

    $pathMatcher = new PathMatcher($blog, "/{$post->variants[0]->slug}");
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    // dates
    expect($content)->toContain("<meta property=\"article:published_time\" content=\"$publishedAt\" />");
    expect($content)->toContain("<meta property=\"article:modified_time\" content=\"$updatedAt\" />");

    // authors
    foreach ($post->authors as $author) {
        $name = htmlspecialchars($author->variants[0]->name);
        expect($content)->toContain("<meta property=\"article:author\" content=\"$name\" />");
    }

    // tags
    foreach ($post->tags as $tag) {
        $name = htmlspecialchars($tag->variants[0]->name);
        expect($content)->toContain("<meta property=\"article:section\" content=\"$name\" />");
    }

    // alternates
    foreach ($post->variants->skip(1) as $variant) {
        $url = PermalinkRepository::getPostPermalink($post, $blog, $variant->language);
        expect($content)->toContain(
            "<link rel=\"alternate\" href=\"$url\" hreflang=\"{$variant->language->code}\" />"
        );
    }

    // twitter
    expect($content)->toContain('<meta name="twitter:creator" content="@Author" />');
    // post code head
    expect($content)->toContain($postCodeHeadRendered);
});

it('adds nofollow', function () {
    $blog = blogWithLanguageAndRoutes();
    $blog->setMeta('seo_indexing', false);

    $post = addPublishedPost($blog);

    $content = '{{ _head | template }}';
    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/{$post->variants[0]->slug}");
    $responseObject = $pathMatcher->getResponseObject();
    $content = $responseObject->content;

    expect($content)->toContain('<meta name="robots" content="noindex">');
});

it('adds favicon', function() {
    $blog = blogWithLanguageAndRoutes();
    $url = 'https://exmaple.com/icon.png';
    $blog->setMeta('icon_url', $url);

    $content = '{{ _head | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain("<link rel=\"shortcut icon\" href=\"$url\" />");
});

it('adds favicon from logo when icon is not set', function() {

    $blog = blogWithLanguageAndRoutes();
    $url = 'https://exmaple.com/logo.png';
    $blog->setMeta('logo_url', $url);

    $content = '{{ _head | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain("<link rel=\"shortcut icon\" href=\"$url\" />");

});

it('adds fonts', function() {

    $blog = blogWithLanguageAndRoutes();

    $content = '{{ _head | template }}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        null,
        'config.yaml',
        'THEME_FONTS: mulish:400'
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    expect($content)->toContain("<link rel=\"stylesheet\" href=\"https://{$blog->subdomain}.hyvorblogs.io/fonts/css/mulish:400\" />");

});

it('adds tag code', function() {

    $blog = blogWithLanguageAndRoutes();

    $post = addPublishedPost($blog);
    $tag = addTag($blog, [
        'code_head' => 'This is tag code head for {{ _post.slug }}'
    ]);
    addTagToPost($post, $tag);

    $otherTag = addTag($blog, ['code_head' => 'Not tag']);

    $content = '{{ _head | template }}';

    // post page
    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/{$post->variants[0]->slug}");
    $responseObject = $pathMatcher->getResponseObject();
    $content = $responseObject->content;

    expect($content)->toContain('This is tag code head for ' . $post->variants[0]->slug);
    expect($content)->not->toContain('Not tag');

});

it('does not add tag code to other pages', function() {

    $blog = blogWithLanguageAndRoutes();

    $post = addPublishedPost($blog);
    $tag = addTag($blog, [
        'code_head' => 'This is tag code head for {{ _post.slug }}'
    ]);
    addTagToPost($post, $tag);

    $content = '{{ _head | template }}';
    // index page
    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();
    $content = $responseObject->content;

    expect($content)->not->toContain('This is tag code head for');

});