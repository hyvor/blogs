<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\NavigationDeleter;
use App\Models\Navigation;
use App\Models\NavigationVariant;

it('deletes navigations and variants', function () {
    $blog = newBlog();

    Navigation::factory()
        ->count(2)
        ->has(
            NavigationVariant::factory()
                ->count(2),
            'variants'
        )
        ->create([
            'blog_id' => $blog,
        ]);

    expect(Navigation::count())->toBe(2);
    expect(NavigationVariant::count())->toBe(4);

    (new NavigationDeleter($blog))->delete();

    expect(Navigation::count())->toBe(0);
    expect(NavigationVariant::count())->toBe(0);
});

it('does not delete navigations of other blogs', function () {
    Navigation::factory()
        ->count(2)
        ->has(
            NavigationVariant::factory()
                ->count(2),
            'variants'
        )
        ->create([
            'blog_id' => blog(),
        ]);

    (new NavigationDeleter(newBlog()))->delete();

    expect(Navigation::count())->toBe(2);
    expect(NavigationVariant::count())->toBe(4);
});
