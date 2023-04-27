<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('does not set _posts and _pagination when posts_filter is null in the route', function () {
    $content = '
        {% if _posts is not defined %}
            posts variable not defined
        {% endif %}
        {% if _pagination is not defined %}
            pagination variable not defined
        {% endif %}
    ';

    $blog = blogWithLanguageAndRoutes();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $post = addPublishedPost($blog);

    $pathMatcher = new PathMatcher($blog, '/'.$post->variants[0]->slug);
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toContain('posts variable not defined');
    expect($responseObject->content)->toContain('pagination variable not defined');
});

it('sets _posts and _pagination', function () {

    $content = '{{ _posts | length }}|{{ _pagination.page }}|{{ _pagination.total }}';

    $blog = blogWithLanguageAndRoutes();
    addPosts($blog, 15, [], ['status' => 'published']);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('10|1|15');
});

it('works with page number', function () {

    $content = '{{ _posts | length }}|{{ _pagination.page }}|{{ _pagination.total }}';

    $blog = blogWithLanguageAndRoutes();
    addPosts($blog, 15, [], ['status' => 'published']);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/page/2');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('5|2|15');
});

it('changes limit based on POSTS_PER_PAGINATION config', function () {

    $content = '{{ _posts | length }}|{{ _pagination.page }}|{{ _pagination.total }}';

    $blog = blogWithLanguageAndRoutes();
    addPosts($blog, 15, [], ['status' => 'published']);

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
        'POSTS_PER_PAGINATION: 5',
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('5|1|15');
});

it('returns 404 when posts are not found for the page number', function () {
    $content = '{{ _posts | length }}';

    $blog = blogWithLanguageAndRoutes();
    addPosts($blog, 15, [], ['status' => 'published']);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/page/3');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(404);
});

it('does not return 404 for the first page even posts are not found', function () {

    $content = '{{ _posts | length }}';

    $blog = blogWithLanguageAndRoutes();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(200);
    expect($responseObject->content)->toBe('0');
});

it('sets featured posts first in _post', function () {

    $blog = blogWithLanguageAndRoutes();
    addPosts($blog, 3, [], ['status' => 'published']);

    $post = $blog->posts[2];
    $post->update(['is_featured' => true]);

    $content = '{% if _posts[0].is_featured %}featured{% endif %}';

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('featured');
});
