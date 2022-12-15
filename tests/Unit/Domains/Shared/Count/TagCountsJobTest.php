<?php

namespace Tests\Unit\Domains\Shared\Count;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Language\LanguageRepository;
use App\Domains\Shared\Count\TagCountsJob;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Tag;

it('counts tag posts for multiple tag', function () {
    $blog = blog();
    (new LanguageFiller($blog))->fill();

    LanguageRepository::createLanguage($blog, 'fr', 'French');

    $tag = Tag::factory()->create(['blog_id' => $blog]);
    $tag1 = Tag::factory()->create(['blog_id' => $blog]);

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
            PostTag::create([
                'post_id' => $post->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    // COUNTED
    $tag1Posts = Post::factory()
        ->count(3)
        ->has(PostVariant::factory()->state([
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ]), 'variants')
        ->create([
            'blog_id' => $blog,
        ]);

    foreach ($tag1Posts as $post) {
        PostTag::create([
            'post_id' => $post->id,
            'tag_id' => $tag1->id,
        ]);
    }

    $job = new TagCountsJob($blog);
    $job->handle();

    $tag->refresh();
    expect($tag->posts_count)->toBe(2);

    $tag1->refresh();
    expect($tag1->posts_count)->toBe(3);
});
