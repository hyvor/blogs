<?php

namespace Tests\Unit\Domains\Blog\Deleters;

use App\Domains\Blog\Deleters\TagDeleter;
use App\Models\Tag;
use App\Models\TagVariant;

beforeEach(function () {
    Tag::truncate();
    TagVariant::truncate();
});

it('deletes tags and variants', function () {
    $blog = newBlog();

    Tag::factory()
        ->count(2)
        ->has(
            TagVariant::factory()
                ->count(2),
            'variants'
        )
        ->create([
            'blog_id' => $blog,
        ]);

    expect(Tag::count())->toBe(2);
    expect(TagVariant::count())->toBe(4);

    (new TagDeleter($blog))->delete();

    expect(Tag::count())->toBe(0);
    expect(TagVariant::count())->toBe(0);
});

it('does not delete tags of other blogs', function () {
    Tag::factory()
        ->count(2)
        ->has(
            TagVariant::factory()
                ->count(2),
            'variants'
        )
        ->create([
            'blog_id' => newBlog(),
        ]);

    (new TagDeleter(newBlog()))->delete();

    expect(Tag::count())->toBe(2);
    expect(TagVariant::count())->toBe(4);
});
