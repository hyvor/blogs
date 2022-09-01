<?php

namespace Tests\Unit\Domains\Shared\Count;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Language\LanguageRepository;
use App\Domains\Shared\Count\AuthorCountsJob;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostVariant;
use App\Models\User;

it('counts author posts for multiple users', function () {
    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    LanguageRepository::createLanguage($blog, 'fr', 'French');

    $user = User::factory()->create(['blog_id' => $blog]);
    $user1 = User::factory()->create(['blog_id' => $blog]);

    // drafts (shouldn't be counted)
    $drafts = Post::factory()
        ->count(2)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'draft',
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
            'is_featured' => true,
        ]);

    // other languages (shouldn't be counted)
    $otherLang = Post::factory()
        ->count(2)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[1]->id,
            'status' => 'published',
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
        ]);

    // pages (shouldn't be counted)
    $pages = Post::factory()
        ->count(2)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'published'
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
            'is_page' => true
        ]);

    // published (COUNTED)
    $published = Post::factory()
        ->count(2)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
        ]);

    foreach ([$drafts, $otherLang, $pages, $published] as $posts) {
        foreach ($posts as $post) {
            PostAuthor::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
        }
    }

    // COUNTED
    $user1Posts = Post::factory()
        ->count(3)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
        ]);

    foreach ($user1Posts as $post) {
        PostAuthor::create([
            'post_id' => $post->id,
            'user_id' => $user1->id,
        ]);
    }

    $job = new AuthorCountsJob($blog);
    $job->handle();

    $user->refresh();
    expect($user->posts_count)->toBe(2);

    $user1->refresh();
    expect($user1->posts_count)->toBe(3);
});
