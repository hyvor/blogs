<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\UserFiller;

it('fills the owner', function () {
    $blog = newBlog();

    (new LanguageFiller($blog))->fill();

    $filler = new UserFiller($blog);
    $filler->fill();

    expect(count($blog->users))->toBe(1);

    $owner = $blog->users[0];

    expect($owner->role)->toBe(UserRoleEnum::OWNER);
});

it('adds more users for dev blogs', function () {
    $blog = newBlog(BlogTypeEnum::DEV);

    (new LanguageFiller($blog))->fill();

    $filler = new UserFiller($blog);
    $filler->fill();

    expect(count($blog->users))->toBe(6);
});


it('adds more users for preview blogs', function () {
    $blog = newBlog(BlogTypeEnum::PREVIEW);

    (new LanguageFiller($blog))->fill();

    $filler = new UserFiller($blog);
    $filler->fill();

    expect(count($blog->users))->toBe(5); // no owner
    expect($blog->users()->where('role', 'owner')->first())->toBeNull();
});
