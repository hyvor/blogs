<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\UserFiller;

it('adds the welcome tag', function() {

    $blog = newBlog();

    (new LanguageFiller($blog))->fill();

    $filler = new TagFiller($blog);
    $filler->fill();

    expect(count($blog->tags))->toBe(1);

    $tag = $blog->tags[0];

    expect($tag->slug)->toBe('welcome');

});

it('adds a few more tags for dev blogs', function() {

    $blog = newBlog(BlogTypeEnum::DEV);

    (new LanguageFiller($blog))->fill();

    $filler = new TagFiller($blog);
    $filler->fill();

    expect(count($blog->tags))->toBe(6);

});