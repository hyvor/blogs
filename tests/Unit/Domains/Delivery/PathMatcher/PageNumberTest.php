<?php

namespace Tests\Unit\Domains\Delivery\Response;


use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Post;
use App\Models\PostVariant;

it('matches index with page number', function () {
    $twig = '{{ _pagination.page }}';
    $result = '2';

    // add posts to make sure there are 2 pages
    Post::factory()
        ->count(25)
        ->has(PostVariant::factory()->state([
            'language_id' => $this->blog->languages[0]->id,
            'status' => 'published'
        ]), 'variants')
        ->create([
            'blog_id' => config('test.blog_id'),
        ]);

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $twig,
    );

    $pathMatcher = new PathMatcher($this->blog, '/page/2');
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($result, $responseObject->content);
});


it('returns not found when pages are not found in larger collections', function () {
    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        '',
    );

    $pathMatcher = new PathMatcher($this->blog, '/page/50000');
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(404, $responseObject->status);
});


it('returns success even when pages are not found but when the page number is 1', function () {
    $twig = '{{ _pagination.total }}';
    $result = '0';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $twig,
    );

    Post::query()->delete();

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($result, $responseObject->content);
});