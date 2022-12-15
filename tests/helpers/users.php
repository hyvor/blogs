<?php

use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @param Language[] $variantLanguages
 */
function addUsers(Blog $blog, int $count = 1, $state = []) {

    $variantLanguages = $blog->languages;

    return User::factory()
        ->count($count)
        ->has(
            UserVariant::factory()
                ->count(count($variantLanguages))
                ->state(new Sequence(
                    ...collect($variantLanguages)->map(fn ($lang) => ['language_id' => $lang])->toArray()
                )),
            'variants'
        )
        ->state($state)
        ->create([
            'blog_id' => $blog,
        ]);

}

function addUser(Blog $blog, $state = []) : User {
    return addUsers($blog, 1, $state)->first();
}

function addAuthorToPost(Post $post, User $user) {

    return PostAuthor::create([
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);

}