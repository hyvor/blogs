<?php
namespace App\Domains\Post;

use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\User;

class PostAuthorRepository {

    public static function create(int $postId, int $userId)
    {
        return PostAuthor::create([
            'post_id' => $postId,
            'user_id' => $userId
        ]);
    }

    public static function deleteAllWithAuthor(int $authorId) 
    {
        PostAuthor::where('user_id', $authorId)
            ->delete();
    }

    public static function getAuthorList(){}

    public static function createAuthor(){}

    public static function createPostAuthor(){}

    public static function getPostAuthor(){}

    public static function removePostAuthor(){}


    public static function getPrimaryAuthor(Post $post) : ?User {
        return $post->authors()->first();
    }
    
}