<?php

namespace Tests\Unit\Domains\Blog\Jobs;

use App\Domains\Blog\Deleters\LanguageDeleter;
use App\Domains\Blog\Deleters\MediaDeleter;
use App\Domains\Blog\Deleters\NavigationDeleter;
use App\Domains\Blog\Deleters\PostDeleter;
use App\Domains\Blog\Deleters\RedirectDeleter;
use App\Domains\Blog\Deleters\RouteDeleter;
use App\Domains\Blog\Deleters\TagDeleter;
use App\Domains\Blog\Deleters\ThemeDeleter;
use App\Domains\Blog\Deleters\UserDeleter;
use App\Domains\Blog\Jobs\DeleteBlogJob;
use App\Models\Blog;

it('calls deleters', function () {

    $deleters = [
        LanguageDeleter::class,
        MediaDeleter::class,
        NavigationDeleter::class,
        PostDeleter::class,
        RedirectDeleter::class,
        RouteDeleter::class,
        TagDeleter::class,
        ThemeDeleter::class,
        UserDeleter::class,
    ];

    // calls the deleters
    foreach ($deleters as $deleter) {
        $mock = mock($deleter)->makePartial();
        $mock->shouldReceive('delete')->once();
        $this->app->bind($deleter, fn () => $mock);
    }

    (new DeleteBlogJob(blog()))->handle();

    // deletes the blog
    expect(Blog::find(config('test.blog_id')))->toBeNull();

});