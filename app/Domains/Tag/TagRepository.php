<?php

namespace App\Domains\Tag;

use App\Models\Tag;
use App\Models\PostTag;
use App\Models\TagsVariant;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Domains\Language\LanguageRepository;

class TagRepository
{    
    public static function getTagByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug) : ?Tag
    {
        $tag = Tag::where('blog_id', $blogId);
        if ($id) {
            $tag->where('id', $id);
        } else {
            $tag->where('slug', $slug);
        }
        return $tag->first();
    }

    public static function getTagByBlogIdAndSlug(int $blogId, string $slug) : ?Tag 
    {
        return self::getTagByBlogIdAndIdentifier($blogId, null, $slug);
    }

    public static function getTags($blog, int $blogId, ?int $limit, int $offset = 0)
    {
        // we should load only the tag data which include in english or the primary language

        $limit = $limit ?? 50;
        $language = LanguageRepository::getPrimaryLanguage($blog);

        $tags = Tag::where('blog_id', '=', $blog->id)
        ->join('tags_variants', function($join) use ($language) {
            $join->on('tags_variants.tag_id', '=', 'tags.id');
            $join->where('tags_variants.language_id', '=',  $language->id);
        })
        ->select('tags.*')
        ->limit($limit)
        ->offset($offset)
        ->latest()
        ->get();

        return $tags;
    }

    public static function createTag( 
        $blog, 
        int $blogId, 
        string $name, 
        string $slug, 
        ?string $description
    ){
        $createTag = Tag::create([
            'blog_id' => $blogId,
            'slug' => $slug,
        ]);

        $tagId = $createTag->id;
        $getLanguage = $blog->languages()->where('is_primary', true)->first();
        $primaryLanguage = $getLanguage->id;

        TagsVariant::create([
            'tag_id' => $tagId,
            'language_id' => $primaryLanguage,
            'name' => $name,
            'description' => $description,
        ]);

        // return [$createTag , $createTagVariant];
    }

    public static function updateTag(
        int $id, 
        int $languageId,
        string $slug,  
        ?string $codeHead,  
        ?string $codeFoot,
        ?string $name,
        ?string $description
    ){

        Tag::find($id)
        ->update([
            'slug' => $slug,
            'code_head' => $codeHead,
            'code_foot' => $codeFoot,
        ]);

        TagsVariant::where('tag_id','=',$id)
            ->where('language_id','=',$languageId)
            ->update([
                'name' => $name,
                'description' => $description,
            ]);
    }

    public static function deleteTag($tagId, $languageId){
        // if it is some other language other oly the data in the variants table should be deleted. (only the variants data should be deleted)
        // if language is the default language the data in tags table and the tags_variants table all should be deleted. (all the data should be deleted.)

        $language = Language::where('id','=', $languageId)
        ->value('is_primary');

        if($language == 0){
            TagsVariant::where('tag_id','=',$tagId)
                ->where('language_id','=',$languageId)
                ->delete();
        }
        else{
            TagsVariant::where('tag_id','=',$tagId)
                ->where('language_id','=',$languageId)
                ->delete();

            Tag::find($tagId)
                ->delete();
        }

    }

    /*
    * 
    * this functions are used for the tag_variants table
    *
    */
    public static function getTagVariant($tagId, $languageId)
    {
        $tags = TagsVariant::where('tag_id', '=', $tagId)
        ->where('language_id', '=', $languageId)
        ->get();
        return $tags;
    }

    public static function createTagVariant($tagId, $languageId){
        // the tag_id, language_id and name and the description should be added
        $language = Language::where('id','=', $languageId)
        ->value('is_primary');

        if($language == 0){
            $tagVariantCheck = TagsVariant::where('tag_id','=', $tagId)
            ->where('language_id','=', $languageId)
            ->first();

            if($tagVariantCheck == null){
                TagsVariant::create([
                    'tag_id' => $tagId,
                    'language_id' => $languageId,
                ]);
            }
        }

    }


    /*
    * 
    * this function will create and save the tag
    *
    */
    public static function createSaveTag($blogId, $postId, $tagId)
    {
        $createPostTag = PostTag::create([
            'post_id' => $postId,
            'tag_id' => $tagId,
        ]);
        return $createPostTag;
    }

    /*
    * 
    * this function will create and save the tag
    *
    */
    public static function getPostTag($postId, $tagId)
    {
        // dd('tests');
        // dd('Post Id '+$postId + ' Tag Id '+$tagId);
        // dd($postId);

        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id' ,$tagId.'', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', $postId.'', '=', 'post_tag.post_id')
        //     ->get();

        $connection = DB::table('tags')
            ->select('tags.name')
            ->join('post_tag', 'tags.id' ,$tagId ,'=', 'post_tag.tag_id')
            ->join('posts', 'posts.id', $postId ,'=', 'post_tag.post_id')
            ->get();
        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', '=', 'post_tag.post_id')
        //     ->get();
        return $connection;
    }
    
}
