<?php

namespace Tests\Unit\Blog\Fillers;

use App\Domains\Blog\Fillers\NavigationFiller;

it('fills with navigations', function() {

    $blog = newBlog();

    $filler = new NavigationFiller();
    $filler->fill();

    $nav = $blog->navigations;

    expect($nav->firstWhere('url', '/about'))->not()->toBeNull();
    expect($nav->firstWhere('url', '/author'))->not()->toBeNull();
    expect($nav->firstWhere('url', '/about'))->not()->toBeNull();

});