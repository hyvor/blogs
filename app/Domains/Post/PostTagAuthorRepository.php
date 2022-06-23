<?php

namespace App\Domains\Post;

use App\Exceptions\TrustedException;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\Tag;
use App\Models\User;

class PostTagAuthorRepository
{
    /**
     * @param Post $post
     * @param array<integer> $ids
     * @return void
     */
    public static function updateTags(Post $post, array $ids)
    {
        $dbCount = Tag::where('blog_id', $post->blog_id)
            ->whereIn('id', $ids)
            ->count();

        if (count($ids) !== $dbCount) {
            throw new TrustedException('Some users are missing or IDs are wrong');
        }

        // remove all
        PostTag::where('post_id', $post->id)->delete();

        // add new
        foreach ($ids as $id) {
            PostTag::create([
                'post_id' => $post->id,
                'tag_id' => $id,
            ]);
        }
    }

    public static function deletePostTagsByTag(Tag $tag)
    {
        PostTag::where('tag_id', $tag->id)->delete();
    }


    /**
     * @param Post $post
     * @param array<integer> $ids
     * @return void
     */
    public static function updateAuthors(Post $post, array $ids)
    {
        $dbCount = User::where('blog_id', $post->blog_id)
            ->whereIn('id', $ids)
            ->count();

        if (count($ids) !== $dbCount) {
            throw new TrustedException('Some users are missing or IDs are wrong');
        }

        // remove all
        PostAuthor::where('post_id', $post->id)->delete();

        // add new
        foreach ($ids as $id) {
            PostAuthor::create([
                'post_id' => $post->id,
                'user_id' => $id,
            ]);
        }
    }

    public static function createAuthor(int $postId, int $userId)
    {
        PostAuthor::create([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
    }

    public static function deletePostAuthorsByUser(User $user)
    {
        PostAuthor::where('user_id', $user->id)->delete();
    }

}
