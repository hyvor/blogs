<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Deleters\LanguageDeleter;
use App\Domains\Blog\Deleters\MediaDeleter;
use App\Domains\Blog\Deleters\NavigationDeleter;
use App\Domains\Blog\Deleters\PostDeleter;
use App\Domains\Blog\Deleters\RedirectDeleter;
use App\Domains\Blog\Deleters\RouteDeleter;
use App\Domains\Blog\Deleters\TagDeleter;
use App\Domains\Blog\Deleters\ThemeDeleter;
use App\Domains\Blog\Deleters\UserDeleter;
use App\Models\Blog;

it('calls the delete blog job', function() {

    $deleters = [
        LanguageDeleter::class,
        MediaDeleter::class,
        NavigationDeleter::class,
        PostDeleter::class,
        RedirectDeleter::class,
        RouteDeleter::class,
        TagDeleter::class,
        ThemeDeleter::class,
        UserDeleter::class
    ];

    // calls the deleters
    foreach ($deleters as $deleter) {
        $mock = mock($deleter)->makePartial();
        $mock->shouldReceive('delete')->once();
        $this->app->bind($deleter, fn() => $mock);
    }

    $this->callConsoleApi('DELETE', '/blog')
        ->assertOk();

    // deletes the blog
    expect(Blog::find(config('test.blog_id')))->toBeNull();

});