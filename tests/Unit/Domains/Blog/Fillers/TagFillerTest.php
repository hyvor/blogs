<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\TagFiller;

it('adds the welcome tag', function () {
    $blog = blog();

    (new LanguageFiller($blog))->fill();

    $filler = new TagFiller($blog);
    $filler->fill();

    expect(count($blog->tags))->toBe(1);

    $tag = $blog->tags[0];

    expect($tag->slug)->toBe('welcome');
});

it('adds a few more tags for dev blogs', function () {
    $blog = devBlog();

    (new LanguageFiller($blog))->fill();

    $filler = new TagFiller($blog);
    $filler->fill();

    expect(count($blog->tags))->toBe(6);
});

it('adds a few more tags for preview blogs', function () {
    $blog = previewBlog();

    (new LanguageFiller($blog))->fill();

    $filler = new TagFiller($blog);
    $filler->fill();

    expect(count($blog->tags))->toBe(6);
});
