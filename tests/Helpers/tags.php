<?php


use App\Models\Blog;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;

function addTags(Blog $blog, int $count = 1, $state = []) {

    $languages = $blog->languages;
    
    return Tag::factory()
        ->count($count)
        ->has(
            TagVariant::factory()
                ->count(count($languages))
                ->state(new Sequence(
                    ...collect($languages)->map(fn ($lang) => ['language_id' => $lang])->toArray()
                )),
            'variants'
        )
        ->state($state)
        ->create([
            'blog_id' => $blog,
        ]);

}

function addTag(Blog $blog, $state = []) : Tag {
    return addTags($blog, 1, $state)->first();
}

function addTagToPost(Post $post, Tag $tag) {

    return PostTag::create([
        'post_id' => $post->id,
        'tag_id' => $tag->id,
    ]);

}