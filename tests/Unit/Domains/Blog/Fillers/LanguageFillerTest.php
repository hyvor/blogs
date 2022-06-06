<?php

namespace Tests\Unit\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\Fillers\LanguageFiller;

it('fills primary language', function () {
    $blog = newBlog();

    $filler = new LanguageFiller($blog);
    $filler->fill();

    $language = $blog->languages[0];

    expect($language->is_primary)->toBeTrue();
    expect($language->code)->toBe('en');
});

it('fills two more languages for dev blogs', function () {
    $blog = newBlog(BlogTypeEnum::DEV);

    $filler = new LanguageFiller($blog);
    $filler->fill();

    expect(count($blog->languages))->toBe(3);
});

it('fills two more languages for preview blogs', function() {

    $blog = newBlog(BlogTypeEnum::PREVIEW);

    $filler = new LanguageFiller($blog);
    $filler->fill();

    expect(count($blog->languages))->toBe(3);

});