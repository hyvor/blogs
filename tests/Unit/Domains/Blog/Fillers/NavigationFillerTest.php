<?php

namespace Tests\Unit\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\NavigationFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\UserFiller;

it('fills with navigations', function() {

    $blog = newBlog();

    $languageFiller = new LanguageFiller($blog);
    $languageFiller->fill();

    $filler = new NavigationFiller($blog);
    $filler->fill();

    $nav = $blog->navigations;

    expect($nav->firstWhere('url', '/about'))->not()->toBeNull();
    expect($nav->firstWhere('url', '/privacy'))->not()->toBeNull();
    expect($nav->firstWhere('url', '/contact'))->not()->toBeNull();

});

it('fills additional for dev blogs', function() {

    $blog = newBlog(BlogTypeEnum::DEV);

    $languageFiller = new LanguageFiller($blog);
    $languageFiller->fill();

    // user is required for author check
    $userFiller = new UserFiller($blog);
    $userFiller->fill();

    $tagFiller = new TagFiller($blog);
    $tagFiller->fill();

    $filler = new NavigationFiller($blog);
    $filler->fill();

    $nav = $blog->navigations;

    $authorSlug = $blog->users[0]->slug;
    $tagSlug = $blog->tags[0]->slug;

    expect($nav->firstWhere('url', "/author/$authorSlug"))->not()->toBeNull();
    expect($nav->firstWhere('url', "/tag/$tagSlug"))->not()->toBeNull();

});