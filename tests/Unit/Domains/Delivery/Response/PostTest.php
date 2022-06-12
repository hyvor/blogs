<?php

namespace Tests\Unit\Domains\Delivery\Response;


use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\PostVariant;

it('matches a post', function () {
    $twig = '{{ _post.id }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $twig,
    );

    $variant = PostVariant::where('post_variants.status', 'published')
        ->join('posts', 'posts.id', '=', 'post_variants.post_id')
        ->where('posts.is_page', false)
        ->where('posts.blog_id', $this->blog->id)
        ->where('post_variants.language_id', $this->blog->languages[0]->id)
        ->select('posts.slug', 'posts.id')
        ->first();

    $pathMatcher = new PathMatcher($this->blog, "/$variant->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals("$variant->id", $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});


it('matches a page', function () {
    $twig = '{{ _post.id }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'page.twig',
        $twig,
    );

    $variant = PostVariant::where('post_variants.status', 'published')
        ->join('posts', 'posts.id', '=', 'post_variants.post_id')
        ->where('posts.is_page', true)
        ->where('posts.blog_id', $this->blog->id)
        ->where('post_variants.language_id', $this->blog->languages[0]->id)
        ->select('posts.slug', 'posts.id')
        ->first();

    $pathMatcher = new PathMatcher($this->blog, "/$variant->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals("$variant->id", $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});

it('matches a post with language', function () {
    $twig = '{{ _post.id }}{{ _lang.id }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'post.twig',
        $twig,
    );

    $variant = PostVariant::where('post_variants.status', 'published')
        ->join('posts', 'posts.id', '=', 'post_variants.post_id')
        ->where('posts.is_page', false)
        ->where('posts.blog_id', $this->blog->id)
        ->where('post_variants.language_id', $this->blog->languages[1]->id)
        ->select('posts.slug', 'posts.id')
        ->first();

    $pathMatcher = new PathMatcher($this->blog, "/{$this->blog->languages[1]->code}/$variant->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals("{$variant->id}{$this->blog->languages[1]->id}", $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});
