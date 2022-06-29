<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\PostDeleter;
use App\Models\PostVariant;
use App\Models\Post;

beforeEach(function () {
    Post::truncate();
    PostVariant::truncate();
});

it('deletes posts and variants', function() {

    $blog = newBlog();

    Post::factory()
        ->count(2)
        ->has(
            PostVariant::factory()
                ->count(2)

            , 'variants')
        ->create([
            'blog_id' => $blog
        ]);

    expect(Post::count())->toBe(2);
    expect(PostVariant::count())->toBe(4);

    (new PostDeleter($blog))->delete();

    expect(Post::count())->toBe(0);
    expect(PostVariant::count())->toBe(0);

});

it('does not delete posts of other blogs', function() {

    Post::factory()
        ->count(2)
        ->has(
            PostVariant::factory()
                ->count(2)

            , 'variants')
        ->create([
            'blog_id' => newBlog()
        ]);

    (new PostDeleter(newBlog()))->delete();

    expect(Post::count())->toBe(2);
    expect(PostVariant::count())->toBe(4);

});