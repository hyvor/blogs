<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;
use App\Domains\User\UserRepository;
use App\Domains\User\UserRepositoryInterface;
use Hyvor\HyvorConnecter\User;

use Illuminate\Http\Request;
use App\Models\Blog;


class ConsoleUserController extends Controller
{

    public function createBlog(Request $request, User $hyvorUser)
    {

        $request->validate([
            'name' => 'required|string',
            'subdomain' => 'required|string',
            'type' => 'string|in:normal,temp,dev'
        ]);

        $name = $request->input('name');
        $subdomain = $request->input('subdomain');
        $type = BlogTypeEnum::from($request->input('type') ?? 'normal');

        $user = BlogRepository::createBlog(
            $hyvorUser->id,     
            $name,
            $subdomain,
            $type
        );

        return response()->json(new UserBlogObject($user));

    }

    public function changeSort(Request $request, User $hyvorUser)
    {

        $request->validate([
            'blog_ids' => 'required|array',
            'blog_ids.*' => 'integer'
        ]);

        $blogIds = $request->input('blog_ids');

        UserRepository::changeBlogSorts($hyvorUser->id, $blogIds);
        
    }

<<<<<<< HEAD
    /*
    *
    * ConsoleAPI Settings->users
    *
    */
    public static function getAuthor(Request $request , Blog $blog)
    {
        // $limit = $request->input('limit');
        // $offset = $request->input('offset') ?? 0;

        // $getData = UserRepository::getTags($blog->id, $limit, $offset)
        //         ->map(function ($tags) {
        //         return new TagObject($tags);
        //     });
        // return response()->json($getData);

        return 'get user';
    }

    public static function createAuthor(Request $request) {

        return 'create Author';
    }

    public static function updateAuthor(Request $request)
    {
       return 'update author';
    }

    public static function deleteAuthor(Request $request)
    {
       return 'delete author';
    }

    /*
    *
    *
    * *** ConsoleAPI Posts->Author ***
    *
    * This function will get all the Authors and display it in an order (Post_Count)
    */
    public static function getAuthorList(Request $request){
        
        return 'get Author list';
    }

    /*
    *
    * This function will save the post_id and the Author_id in the post_Author table.
    * (This function should also save the number of posts in the count table.)
    *
    */
    public static function createPostAuthor(Request $request){
        
        return 'create post Author';
    }

    /*
    * 
    * This function will get the selected Authors and display it in the react-select box.
    *
    */
    public static function getPostAuthor(Request $request){

        return 'get post Author';
    }

    /*
    * 
    * This function will remove the selected Author.
    *
    */
    public static function removePostAuthor(Request $request){
       return 'hello world';
    }


=======
    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string'
        ]);

        $subdomain = $request->input('subdomain');

        $blog = BlogRepository::getBlogBySubdomain($subdomain);

        return response()->json($blog ? false : true);

    }

>>>>>>> master
}
