<?php

namespace Tests\Unit\Delivery\PathMatcher;

// depends on default routes

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\RouteRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Post;
use App\Models\PostVariant;

it('matches index page', function() {

    $content = 'Hello World';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);

});

it('matches tag page', function() {

    $content = 'I am a tag';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'tag.twig',
        $content,
    );

    $tag = $this->blog->tags[0];

    $pathMatcher = new PathMatcher($this->blog, "/tag/$tag->slug");
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);

});


it('matches author page', function() {

    $content = 'I am an author';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'author.twig',
        $content,
    );

    $user = $this->blog->users[0];

    $pathMatcher = new PathMatcher($this->blog, "/author/$user->slug");
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);

});

it('matches custom route', function() {

    RouteRepository::createRoute(
        $this->blog,
        'test',
        '/test',
        'test',
    );

    $content = 'I am a custom route';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'test.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, "/test");
    $responseObject =  $pathMatcher->getResponseObject();


    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);

});

it('matches index with page number', function() {

    $twig = '{{ _pagination.page }}';
    $result = '2';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $twig,
    );

    $pathMatcher = new PathMatcher($this->blog, '/page/2');
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($result, $responseObject->content);

});

it('returns not found when pages are not found in larger collections', function() {

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        '',
    );

    $pathMatcher = new PathMatcher($this->blog, '/page/50000');
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(404, $responseObject->status);

});

it('returns success even when pages are not found but when the page number is 1', function() {

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
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals($result, $responseObject->content);

});


it('matches index with feed', function() {

    $pathMatcher = new PathMatcher($this->blog, '/feed');
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals('application/atom+xml', $responseObject->mime_type);

});

it('matches a post', function() {

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
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals("$variant->id", $responseObject->content);

});

it('matches a page', function() {

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
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals("$variant->id", $responseObject->content);

});

it('matches a post with language', function() {

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
    $responseObject =  $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals(200, $responseObject->status);
    $this->assertEquals("{$variant->id}{$this->blog->languages[1]->id}", $responseObject->content);

});