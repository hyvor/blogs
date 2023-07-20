<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\PostFiller\PostFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\UserFiller;
use App\Models\Post;

it('fills with posts', function () {
    $blog = blog();

    (new LanguageFiller($blog))->fill();
    (new UserFiller($blog))->fill();
    (new TagFiller($blog))->fill();

    $filler = new PostFiller($blog);
    $filler->fill();

    $posts = $blog->posts;
    expect(count($posts))->toBe(5);

    expect($posts->firstWhere(fn($p) => $p->variants[0]['slug'] === 'welcome'))->toBeInstanceOf(Post::class);
    expect($posts->firstWhere(fn($p) => $p->variants[0]['slug'] === 'content-style'))->toBeInstanceOf(Post::class);
    expect($posts->firstWhere(fn($p) => $p->variants[0]['slug'] === 'about'))->toBeInstanceOf(Post::class);
    expect($posts->firstWhere(fn($p) => $p->variants[0]['slug'] === 'privacy'))->toBeInstanceOf(Post::class);
    expect($posts->firstWhere(fn($p) => $p->variants[0]['slug'] === 'contact'))->toBeInstanceOf(Post::class);

    $post1 = $posts[0];

    expect($post1->tags[0]->slug)->toBe('welcome');
    expect($post1->authors[0]->id)->toBe($blog->users[0]->id);
});

it('adds more posts for DEV blogs', function () {
    $blog = devBlog();

    (new LanguageFiller($blog))->fill();
    (new UserFiller($blog))->fill();
    (new TagFiller($blog))->fill();

    $filler = new PostFiller($blog);
    $filler->fill();

    expect($blog->posts()->count())->toBeGreaterThan(50);

    // get latest posts because, the first few  posts are default posts and does not have variants
    $post = $blog->posts()->latest('id')->first();
    expect($post->variants()->count())->toBe(3);
    $variant = $post->variants[0];
    expect($variant->content_html)->not->toBeNull();
    expect($post->tags()->count())->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(3);
    expect($post->authors()->count())->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(3);
});

it('adds more posts for preview blogs', function () {
    $blog = previewBlog();

    (new LanguageFiller($blog))->fill();
    (new UserFiller($blog))->fill();
    (new TagFiller($blog))->fill();

    $filler = new PostFiller($blog);
    $filler->fill();

    expect($blog->posts()->count())->toBeGreaterThan(30);
});
