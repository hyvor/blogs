<?php

namespace App\Domains\Tag;

use App\Models\Tag;
use App\Models\PostTag;
use App\Models\TagsVariant;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class TagRepository
{
    public static function getTagByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug)
    {
        $post = Tag::where('blog_id', $blogId);
        if ($id) {
            $post->where('id', $id);
        } else {
            $post->where('slug', $slug);
        }
        return $post->first();
    }

    // public static function getTags(int $blogId, ?int $limit, int $offset = 0, int $languageId)
    public static function getTags(int $blogId, ?int $limit, int $offset = 0)
    {
        $limit = $limit ?? 50;

        // The query without the language.
        // $tags = Tag::where('blog_id','=', $blogId)
        //     ->limit($limit)
        //     ->offset($offset)
        //     ->latest()
        //     ->get();

        // The query with the language filter.
        // $language = Language::where('blog_id','=', $blogId)
        // ->where('id','=', $languageId)
        // ->value('languages.id');

        // $tags = Tag::where('blog_id', '=', $blogId)
        // ->join('tags_variants', function($join) use ($language) {
        //     $join->on('tags_variants.tag_id', '=', 'tags.id');
        //     $join->where('tags_variants.language_id', '=', $language);
        // })
        // ->limit($limit)
        // ->offset($offset)
        // ->latest()
        // ->get();

        // The query without the language filter.
        $tags = Tag::where('blog_id', '=', $blogId)
        ->join('tags_variants', function($join) {
            $join->on('tags_variants.tag_id', '=', 'tags.id');
            $join->select('tags.slug');
        })
        ->limit($limit)
        ->offset($offset)
        ->latest()
        ->get();

        return $tags;

    }

    public static function createTag( int $blogId, string $name, string $slug, ?string $description)
    {
        $createTag = Tag::create([
            'blog_id' => $blogId,
            'slug' => $slug,
        ]);

        $createTagVariant = TagsVariant::create([
            'tag_id' => 1,
            'language_id' => 1,
            'name' => $name,
            'description' => $description,
        ]);


        // $createTag = 'hell create one';
        // $createTagVarient = 'hello create two';

        return [$createTag , $createTagVariant];
    }

    public static function updateTag(int $id, string $name, string $slug, ?string $description,  ?string $codeHead,  ?string $codeFoot)
    {
        $tag = Tag::find($id);
        $tag->name=$name;
        $tag->slug=$slug;
        $tag->description=$description; 
        $tag->code_head=$codeHead; 
        $tag->code_foot=$codeFoot; 

        $tag->save(); 
    }

    public static function deleteTag(int $id)
    {
        // dd($id);
        $data = Tag::find($id);
        $data->delete();
    }

    /*
    * 
    * this function will create and save the tag
    *
    */
    public static function createSaveTag($blogId, $postId, $tagId){
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
    public static function getPostTag($postId, $tagId){
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
