<?php

namespace App\Domains\Post;

use App\Models\Tag;
use App\Models\PostTag;
use App\Models\Post;

use Illuminate\Support\Facades\DB;

class PostTagRepository
{
    public static function selectedPostTag(int $blogId, int $postId){
        // dd($tagId);

        $postTag = PostTag::where('post_id', '=', $postId)->pluck('tag_id')->all();

        $tagIdList = array();
        foreach($postTag as $value)
        {
            $tagIdList[] = Tag::where('id', '=', $value)
            ->select('name')
            ->first();
        }

        return $tagIdList;
    }




    public static function getTagList(int $blogId, int $postId)
    {
        $post = Post::where('id', '=', $postId)
        ->value('id');

        $postTag = PostTag::where('post_id', '=', $post)->pluck('tag_id')->toArray();

        $tagIdList = array();
        for ($i=0, $len=count($postTag); $i<$len; $i++) {
            $tagIdList[] = $postTag[$i];
        }

        $getList = DB::table('tags')->select('tags.name', 'tags.id')->whereNotIn('id', $tagIdList)->get();
        return $getList;


        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', '=', 'post_tag.post_id')
        //     ->get();


        // $tagIdList = DB::table('tags')
        // ->select('tags.name')
        // ->where('post_tag','post_tag.post_id', '=', $post)
        // ->leftJoin('post_tag','post_tag.tag_id','=','tags.id')
        // ->whereNull('post_tag.tag_id')
        // ->get();

    }

    public static function createTag( int $blogId, string $name, string $slug, ?string $description)
    {
        $createTag = Tag::create([
            'blog_id' => $blogId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);
        return $createTag;
    }

    /*
    * 
    * this function will create and save the tag
    *
    */
    public static function createSaveTag($postId, $tagId){
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

        $posts = Post::where('id', '=', $postId)
        ->value('id');

        $tag = Tag::where('id', '=', $tagId)
        ->value('id');

        $postTag = PostTag::where('post_id', '=', $posts)->pluck('tag_id')->all();

        $tagIdList = array();
        foreach($postTag as $value)
        {
            $tagIdList[] = Tag::where('id', '=', $value)
            ->select('name')
            ->first();
        }

        return $tagIdList;




        

        // dd('tests');
        // dd('Post Id '+$postId + ' Tag Id '+$tagId);
        // dd($postId);

        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id' ,$tagId.'', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', $postId.'', '=', 'post_tag.post_id')
        //     ->get();

        // $connection = DB::table('tags')
        //     // ->select('tags.name')
        //     ->join('post_tag', 'tags.id' ,$tagId ,'=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', $postId ,'=', 'post_tag.post_id')
        //     ->where('tags.name')
        //     ->get();

        // $connection = DB::table('post_tag')
        // ->join('posts', 'posts.id', '=', 'post_tag.post_id')
        // ->join('follows', 'follows.user_id', '=', 'users.id')
        // ->where('follows.follower_id', '=', 3)
        // ->get();

        // $connection = DB::table('tags')
        //     ->select('tags.name')
        //     ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
        //     ->join('posts', 'posts.id', '=', 'post_tag.post_id')
        //     ->get();

        // dd('test');

        // if($posts == $postId && $tag == $tagId){
        //     $postTag = PostTag::where('post_id', '=', $posts)
        //     ->where('tag_id', '=', $tag)
        //     ->get();

        //     if($postTag != null){
        //         $tag = Tag::where('id', '=', $tagId)
        //         ->select('name')
        //         ->get();

        //         return $tag;
        //     }
        // }


        // $postTag = PostTag::where('post_id', '=', $posts)
        // ->where('tag_id', '=', $tag)
        // ->value('tag_id');

        // ->lists('tag_id')
        // ->get();

        // dd($postTag);
        // foreach($tagIdList as $data)
        // {
        //     return $data;
        // }

        // dd($tagIdList);
        // extract($tagIdList, EXTR_PREFIX_ALL, $connection);

        // dd($connection);

        // dd($connection);
        // return $connection;

        // for ($i=0, $len=count($postTag); $i<$len; $i++) {
        //     echo $postTag[$i];
        //     $cc = $postTag[$i];
        // }

        // dd($cc);
        // $connection = Tag::where('id', '=', $cc)
        //     ->value('name');
        //     return $connection; 


        // return 'test';
        // $connection = Tag::where('id', '=', $postTag)
        // ->value('name');
        // return $connection;
    }
    
}
