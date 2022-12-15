<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\UserDeleter;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;


it('deletes users', function () {
    $blog = blog();

    User::factory()
        ->count(2)
        ->state(new Sequence(
            ['role' => 'owner'],
            ['role' => 'admin']
        ))
        ->has(
            UserVariant::factory()
                ->count(2),
            'variants'
        )
        ->create([
            'blog_id' => $blog,
        ]);

    expect(User::count())->toBe(2);
    expect(UserVariant::count())->toBe(4);

    (new UserDeleter($blog))->delete();

    expect(User::count())->toBe(0);
    expect(UserVariant::count())->toBe(0);
});

it('does not delete users of other blogs', function () {
    User::factory()
        ->count(2)
        ->has(
            UserVariant::factory()
                ->count(2),
            'variants'
        )
        ->create([
            'blog_id' => blog(),
        ]);

    (new UserDeleter(blog()))->delete();

    expect(User::count())->toBe(2);
    expect(UserVariant::count())->toBe(4);
});
