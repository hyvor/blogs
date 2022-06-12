<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Post;

it('does not set _posts and _pagination when posts_filter is null in the route', function() {

    $content = "
        {% if _posts is not defined %}
            posts variable not defined
        {% endif %}
        {% if _pagination is not defined %}
            pagination variable not defined
        {% endif %}
    ";

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $content,
    );

    $post = aPublishedPost();

    $pathMatcher = new PathMatcher($this->blog, '/' . $post->slug);
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toContain('posts variable not defined');
    expect($responseObject->content)->toContain('pagination variable not defined');

});

it('sets _posts and _pagination', function() {

    clearPosts();
    seedPublishedPosts(15);

    $content = '{{ _posts | length }}|{{ _pagination.page }}|{{ _pagination.total }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('10|1|15');

});

it('works with page number', function() {

    clearPosts();
    seedPublishedPosts(25);

    $content = '{{ _posts | length }}|{{ _pagination.page }}|{{ _pagination.total }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/page/2');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('10|2|25');

});

it('changes limit based on POSTS_PER_PAGINATION config', function() {

    clearPosts();
    seedPublishedPosts(15);

    $content = '{{ _posts | length }}|{{ _pagination.page }}|{{ _pagination.total }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        null,
        'config.yaml',
        'POSTS_PER_PAGINATION: 5',
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('5|1|15');


});

it('returns 404 when posts are not found for the page number', function() {
    clearPosts();
    seedPublishedPosts(15);

    $content = '{{ _posts | length }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/page/3');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(404);
});

it('does not return 404 for the first page even posts are not found', function() {

    clearPosts();

    $content = '{{ _posts | length }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(200);
    expect($responseObject->content)->toBe('0');

});