<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Tag\TagRepository;
use App\Data\Objects\ConsoleAPI\Tag\TagObject;
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
        // $request->validate([
        //     'limit' => 'integer', 
        //     'offset' => 'required|integer', 
        // ]);
        
        $limit = $request->input('limit');
        $offset = $request->input('offset') ?? 0;

        $getData = TagRepository::getTags($blog, $limit, $offset)
                ->map(function ($tags) use ($blog) {
                    return new TagObject($tags, $blog);
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

        $createTag = TagRepository::createTag($blog, $name, $slug, $description); 
        return response()->json($createTag);
        // return response()->json(new TagObject($createTag));
    }

    public static function updateTag(Request $request, Blog $blog)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'slug' => 'required|string',
        //     'description' => 'required|int',
        //     'codeHead' => 'required|string',
        //     'codeFoot' => 'required|string',
        // ]);

        $id = $request->route('id');
        $slug = $request->input('slug');
        $languageId = $request->input('languageId');
        $codeHead = $request->input('codeHead') ?? null;
        $codeFoot = $request->input('codeFoot') ?? null;
        $name = $request->input('name') ?? null;
        $description = $request->input('description') ?? null;
        
        $updateOldTag = TagRepository::updateTag($id, $languageId, $slug, $codeHead, $codeFoot, $name, $description );
        return response()->json($updateOldTag);
    }

    /*
    *
    * *** Tag validation section ***
    *
    */
    // public static function getTagVariant(Request $request)
    // {
    //     $tagId =(int) $request->get('tagId');
    //     $languageId =(int) $request->input('languageId');

    //     $getVariant = TagRepository::getTagVariant($tagId, $languageId);

    //     return response()->json($getVariant);
    // }

    public static function createTagVariant(Request $request)
    {
        $tagId = $request->input('tagId');
        $languageId = $request->input('languageId');
        $createVariant = TagRepository::createTagVariant($tagId, $languageId);

        return response()->json($createVariant);
    }

    // public static function updateTagVariant(Request $request)
    // {
    //     $tagId = $request->input('tagId');
    //     $languageId = $request->input('languageId');
    //     $name = $request->input('name');
    //     $description = $request->input('description');

    //     $updateVariant = TagRepository::updateTagVariant($tagId, $languageId, $name, $description);

    //     return response()->json($updateVariant);
    // }

    public static function deleteTag(Request $request)
    {
        $tagId = $request->route('tagId');
        $languageId = $request->input('languageId');

        $deleteVariant = TagRepository::deleteTag($tagId, $languageId);

        return response()->json($deleteVariant);
    }


    
    /*
    *
    *
    * *** ConsoleAPI Posts->Tags ***
    *
    * This function will get all the tags and display it in an order (Post_Count)
    */
    public static function getPostTags(Request $request, Blog $blog){
        
        // $postId = $request->input('postId');

        $postId = 184;
        $getData = PostTagRepository::getPostTags($blog->id, $postId);
        return response()->json($getData);
    }

    public static function selectedPostTag(Request $request, Blog $blog){

        $postId = (int) $request->route('postId');
        $getData = PostTagRepository::selectedPostTag($blog->id, $postId);
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