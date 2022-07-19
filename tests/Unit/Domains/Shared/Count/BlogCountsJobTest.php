<?php

namespace Tests\Unit\Domains\Shared\Count;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Shared\Count\BlogCountsJob;
use App\Models\Post;
use App\Models\PostVariant;

it('updates blog post counts', function() {

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    // drafts & featured
    Post::factory()
        ->count(2)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'draft'
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
            'is_featured' => true
        ]);

    // scheduled
    Post::factory()
        ->count(3)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'scheduled'
        ]), 'variants')
        ->create([
            'blog_id' => $blog
        ]);

    Post::factory()
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'published'
        ]), 'variants')
        ->create([
            'blog_id' => $blog
        ]);

    BlogCountsJob::dispatch($blog);

    $blog->refresh();

    expect($blog->getCount('posts'))->toBe(1);
    expect($blog->getCount('posts_draft'))->toBe(2);
    expect($blog->getCount('posts_scheduled'))->toBe(3);
    expect($blog->getCount('posts_featured'))->toBe(2);

});