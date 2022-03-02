<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Tag\TagRepository;
use App\Data\Objects\ConsoleAPI\TagObject;
use App\Models\Blog;

class ConsoleTagController extends Controller {

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
    * this function will create and save the tag
    *
    */
    public static function createPostTag(Request $request , Blog $blog){
        // dd('test create');
        $postId = $request->input('postId');
        $tagId = $request->input('tagId');

        $createTag = TagRepository::createSaveTag($blog->id, $postId, $tagId); 
        return response()->json($createTag);
    }

    /*
    * 
    * this function will create and save the tag
    *
    */
    public static function getPostTag(Request $request){
        // dd('test get');

        // $postId = $request->input('postId');
        // $tagId = $request->input('tagId');

        $postId = 97;
        $tagId = 1;

        $getData = TagRepository::getPostTag($postId, $tagId);
        return response()->json($getData);
    }
}