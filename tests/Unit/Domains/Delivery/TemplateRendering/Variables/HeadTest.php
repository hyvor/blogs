<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _head in index', function () {
    $blog = blog();
    $variant = $blog->variants[0];
    $blogUrl = PermalinkRepository::getBlogPermalink($blog, $blog->languages[0]);

    $codeHead = 'This is code head {{ _blog.name }}';
    $codeHeadRendered = "This is code head $variant->name";

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
    expect($content)->toContain("<title>$variant->name</title>");
    // description
    expect($content)->toContain("<meta name=\"description\" content=\"$variant->description\" />");
    // canonical
    expect($content)->toContain("<link rel=\"canonical\" href=\"$blogUrl\" />");
    // twitter
    expect($content)->toContain('<meta name="twitter:site" content="@HyvorBlogs" />');
});

it('sets _head in a post page', function () {
    $blog = blog();
    $post = aPublishedPost();

    $postCodeHead = 'A post code head {{ _post.id }}';
    $postCodeHeadRendered = "A post code head $post->id";
    $post->update(['code_head' => $postCodeHead]);

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

    $pathMatcher = new PathMatcher($blog, "/$post->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $content = $responseObject->content;

    // dates
    expect($content)->toContain("<meta property=\"article:published_time\" content=\"$publishedAt\" />");
    expect($content)->toContain("<meta property=\"article:modified_time\" content=\"$updatedAt\" />");

    // authors
    foreach ($post->authors as $author) {
        $name = $author->variants[0]->name;
        expect($content)->toContain("<meta property=\"article:author\" content=\"$name\" />");
    }

    // tags
    foreach ($post->tags as $tag) {
        $name = $tag->variants[0]->name;
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
    expect($content)->toContain("<meta name=\"twitter:creator\" content=\"@Author\" />");
    // post code head
    expect($content)->toContain($postCodeHeadRendered);
});


it('adds nofollow', function() {

    $blog = blog();
    $blog->setMeta('seo_indexing', false);

    $post = aPublishedPost();

    $content = '{{ _head | template }}';
    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/$post->slug");
    $responseObject = $pathMatcher->getResponseObject();
    $content = $responseObject->content;

    expect($content)->toContain('<meta name="robots" content="noindex">');

});