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
use Database\Factories\BlogFactory;
use Hyvor\Internal\Resource\ResourceFake;
use Tests\Case\DatabaseTestCase;

class DeleteBlogJobTest extends DatabaseTestCase
{

    public function testDeletes(): void
    {
        ResourceFake::enable();

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
            /** @var mixed $mock */
            $mock = \Mockery::mock($deleter);
            $mock->shouldReceive('delete')->once();
            $this->app->bind($deleter, fn() => $mock);
        }

        $blog = BlogFactory::one();
        (new DeleteBlogJob($blog))->handle();

        $this->assertNull(Blog::find($blog->id));

        ResourceFake::assertDeleted($blog->id);
    }

}
