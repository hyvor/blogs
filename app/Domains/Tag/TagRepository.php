<?php

namespace App\Domains\Tag;

use App\Models\Tag;
use App\Models\PostTag;
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

    public static function getTags(int $blogId, ?int $limit, int $offset = 0)
    {
        $limit = $limit ?? 50;
        $tags = Tag::where('blog_id','=', $blogId)
            ->limit($limit)
            ->offset($offset)
            ->latest()
            ->get();
        
            // dd("tags");
        return $tags;
    }

    public static function createTag( int $blogId, string $name, string $slug, $description = NULL, $featuredImage = NULL , $postsCount = NULL)
    {
        // dd('hello');
        // dd($description . ' ' . $slug . '');
        // dd('test value - '.$description);
        // dd($blogId);
        // dd($featuredImage);
        // dd($slug.' '. $name);
        $createTag = Tag::create([
            'blog_id' => $blogId,
            'name' => $name,
            'slug' => $slug,
            // 'description' => $description,
            // 'featured_image' => $featuredImage,
            // 'posts_count' => $postsCount,

        ]);
        // dd($createTag);
        return $createTag;
    }

    public static function updateTag(int $id, string $name, string $slug, $description, $featuredImage, $postsCount)
    {
        // dd('test'. $id);
        $tag = Tag::find($id);
        $tag->name=$name;
        $tag->slug=$slug;
        // $tag->description=$description;
        // $tag->featuredImage=$featuredImage;
        // $tag->postsCount=$postsCount;

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
        // dd('another test');
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
