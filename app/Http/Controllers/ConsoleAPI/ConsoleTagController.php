<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Tag\TagRepository;
use App\Data\Objects\ConsoleAPI\TagObject;
use App\Models\Blog;
use App\Domains\Post\PostTagRepository;

class ConsoleTagController extends Controller {

    /*
    *
    * ConsoleAPI Settings->tags
    *
    */
    public static function getTag(Request $request, Blog $blog)
    {
        // dd('test');
        // $request->validate([
        //     'limit' => 'integer', 
        //     'offset' => 'required|integer', 
        // ]);
        
        $limit = $request->input('limit');
        $offset = $request->input('offset') ?? 0;

        $getData = TagRepository::getTags($blog->id, $limit, $offset)
                ->map(function ($tags) {
                return new TagObject($tags);
            });
        return response()->json($getData);
    }

    public static function createTag(Request $request , Blog $blog) {

        // $request->validate([
        //     'name' => 'required|string',
        //     'slug' => 'required|string',
        //     'description' => 'required|int',
        // ]);

        $name = $request->input('name');
        $slug = $request->input('slug');
        $description = $request->input('description') ?? null;

        if($slug == null){
            $slug = str_replace(" ", "-", $name);
        }

        $createTag = TagRepository::createTag($blog->id, $name, $slug, $description); 
        return response()->json($createTag);
        // return response()->json(new TagObject($createTag));
    }

    public static function updateTag(Request $request, Blog $blog)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'slug' => 'required|string',
        //     'description' => 'required|int',
        // ]);

        $id = $request->route('tagId');
        $name = $request->input('name');
        $slug = $request->input('slug');
        $description = $request->input('description') ?? null;

        // $description = null;
        // $featuredImage = null;
        // $postsCount = null;
        
        $updateOldTag = TagRepository::updateTag($id, $name, $slug, $description);
        // $updateTag = TagRepository::updateTag($id, $name, $slug, $description);
        return response()->json($updateOldTag);
    }

    public static function deleteTag(Request $request)
    {
        $id = $request->route('tagId');
        $deleteTag = TagRepository::deleteTag($id);

        return response()->json($deleteTag);
    }

    /*
    *
    *
    * *** ConsoleAPI Posts->Tags ***
    *
    * This function will get all the tags and display it in an order (Post_Count)
    */
    public static function getTagList(Request $request, Blog $blog){
        
        // $postId = $request->input('postId');

        $postId = 184;
        $getData = PostTagRepository::getTagList($blog->id, $postId);
        return response()->json($getData);
    }

    /*
    *
    * This function will save the post_id and the tag_id in the post_tag table.
    * (This function should also save the number of posts in the count table.)
    *
    */
    public static function createPostTag(Request $request , Blog $blog){
        dd('test');
        $postId = $request->input('postId');
        $tagId = $request->input('tagId');

        $createTag = PostTagRepository::createSaveTag($postId, $tagId); 
        return response()->json($createTag);
    }

    /*
    * 
    * This function will get the selected tags and display it in the react-select box.
    *
    */
    public static function getPostTag(Request $request){

        $postId = $request->input('postId');

        // $tagId = $request->input('tagId');
        // $postId = 184;
        $tagId = 12;

        $getData = PostTagRepository::getPostTag($postId, $tagId);
        return response()->json($getData);
    }

    /*
    * 
    * This function will remove the selected tags.
    *
    */
    public static function removePostTag(Request $request){
       return 'hello world';
    }
}