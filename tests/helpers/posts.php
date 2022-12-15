<?php


use App\Models\Blog;
use App\Models\Post;
use App\Models\Language;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Arr;

function addPosts(Blog $blog, int $count, $state = [], $variantState = [])
{

    $languages = $blog->languages;

    return Post::factory()
        ->count($count)
        ->has(
            PostVariant::factory()
                ->count($languages->count())
                ->state(new Sequence(
                    ...$languages->map(fn ($language) => [
                        'language_id' => $language->id,
                    ])->toArray()
                ))
                ->state($variantState),
            'variants'
        )
        ->state($state)
        ->create([
            'blog_id' => $blog
        ]);

}

function addPost(Blog $blog, $state = [], $variantState = []) : Post {
    return addPosts($blog, 1, $state, $variantState)->first();
}

function addPublishedPost(Blog $blog, $state = [], $variantState = []) : Post {
    return addPost($blog, $state, array_merge(['status' => 'published'], $variantState));
}