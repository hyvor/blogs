<?php

namespace App\Domains\Import;

use App\Models\Import;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Models\User;
use App\Models\UserVariant;
use App\Models\Tag;
use App\Models\TagVariant;

class Importer
{
    public static function import()
    {}

    public static function blogData(Blog $blog, ?string $blogTitle, ?string $blogDescription, ?string $blogLanguage){
        BlogVariant::where([
            'blog_id' => $blog->id,
            'language_id'=> 1,
        ]) ->update([
            'name' => $blogTitle ?? null,
            'description' => $blogDescription  ?? null,
        ]);
    }

    public static function authorData(
        Blog $blog, ?string $name, ?string $email, ?string $slug, $role, $status, $authorCount
    ){
        $user = User::create([
            'blog_id' => $blog->id,
            'slug' => $slug, 
            'status' => $status->value,
            'role' => $role->value,
            'email' => $email,
        ]);

        $getLanguage = $blog->languages()->where('is_primary', true)->first();

        UserVariant::create([
            'user_id' => $user->id,
            'language_id' => $getLanguage->id,
            'name' => $name,
        ]);

        Import::where([
            'blog_id' => $blog->id,
            'language_id'=> $getLanguage->id,
        ]) ->update([
            'author_count' => $authorCount ?? null,
        ]);
    }

    public static function tagData(Blog $blog, ?string $name, ?string $slug, ?string $tagCount){
        
        $tag = Tag::create([
            'blog_id' => $blog->id,
            'slug' => $slug,
        ]);

        $getLanguage = $blog->languages()->where('is_primary', true)->first();
        $primaryLanguage = $getLanguage->id;

        TagVariant::create([
            'tag_id' => $tag->id,
            'language_id' => $primaryLanguage,
            'name' => $name,
        ]);

        Import::where([
            'blog_id' => $blog->id,
            'language_id'=> $getLanguage->id,
        ]) ->update([
            'tag_count' => $tagCount ?? null,
        ]);
    }

    public static function postData(Blog $blog){}


}