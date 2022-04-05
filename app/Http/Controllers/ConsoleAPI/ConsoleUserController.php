<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;

use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;
use App\Domains\User\UserRepositoryInterface;
use Hyvor\HyvorConnecter\User;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Data\Objects\ConsoleAPI\User\UserObject;



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

    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string'
        ]);

        $subdomain = $request->input('subdomain');

        $blog = BlogRepository::getBlogBySubdomain($subdomain);

        return response()->json($blog ? false : true);

    }

    /*
    *
    * ConsoleAPI Settings->users
    *
    */

    // we should get the user data from the default language.

    // If the user is adding or creating a user that user should also be in the default language.
    // user should be able to add a user or create a guest user. and also user should be able to add a hyvor user too.

    // user should be able to delete or block a user.
    // user also should be able to delete an specific language. ( But if the default language is deleted the user should be deleted)

    // user also should have the access to switch between languages. and if the user switch between language for the first time the language should be created.

    // user should be able to update user name and etc according to the language.
    

    public static function getAuthor(Blog $blog)
    {
        $getData = UserRepository::getAuthor($blog)
                ->map(function ($users) use ($blog) {
                    return new UserObject($users, $blog);
                });
        return response()->json($getData); 
    }

    public static function createAuthor(Request $request, Blog $blog) {

        $role = UserRoleEnum::from($request->input('role'));
        $status =  UserStatusEnum::from($request->input('status'));

        if ($request->has('slug')) {
            $userData['slug'] = $request->input('slug');
        }
        if ($request->has('email')) {
            $userData['email'] = $request->input('email');
        }
        if ($request->has('picture')) {
            $userData['picture'] = $request->input('picture');
        }
        if ($request->has('url')) {
            $userData['url'] = $request->input('url');
        }

        if ($request->has('social_facebook')) {
            $userData['social_facebook'] = $request->input('social_facebook');
        }
        if ($request->has('social_twitter')) {
            $userData['social_twitter'] = $request->input('social_twitter');
        }

        if ($request->has('social_linkedin')) {
            $userData['social_linkedin'] = $request->input('social_linkedin');
        }

        if ($request->has('social_youtube')) {
            $userData['social_youtube'] = $request->input('social_youtube');
        }

        if ($request->has('social_instagram')) {
            $userData['social_instagram'] = $request->input('social_instagram');
        }

        // Variants
        if ($request->has('name')) {
            $userData['name'] = $request->input('name');
        }

        if ($request->has('bio')) {
            $userData['bio'] = $request->input('bio');
        }

        if ($request->has('location')) {
            $userData['location'] = $request->input('location');
        }

        if($userData['slug'] == null){
            $userData['slug'] = str_replace(" ", "-", $userData['name']);
        }

        $createUser = UserRepository::createUser($blog, $blog->user_id, $role, $status, $userData); 
        return response()->json($createUser);
    }

    public static function updateAuthor(Request $request, Blog $blog)
    {
        $userId = $request->route('id');
        $languageId =  $request->input('languageId');
        $role = UserRoleEnum::from($request->input('role'));
        $status =  UserStatusEnum::from($request->input('status'));

        if ($request->has('slug')) {
            $userData['slug'] = $request->input('slug');
        }
        if ($request->has('email')) {
            $userData['email'] = $request->input('email');
        }
        if ($request->has('picture')) {
            $userData['picture'] = $request->input('picture');
        }
        if ($request->has('url')) {
            $userData['url'] = $request->input('url');
        }

        if ($request->has('social_facebook')) {
            $userData['social_facebook'] = $request->input('social_facebook');
        }
        if ($request->has('social_twitter')) {
            $userData['social_twitter'] = $request->input('social_twitter');
        }

        if ($request->has('social_linkedin')) {
            $userData['social_linkedin'] = $request->input('social_linkedin');
        }

        if ($request->has('social_youtube')) {
            $userData['social_youtube'] = $request->input('social_youtube');
        }

        if ($request->has('social_instagram')) {
            $userData['social_instagram'] = $request->input('social_instagram');
        }

        // Variants
        if ($request->has('name')) {
            $userData['name'] = $request->input('name');
        }

        if ($request->has('bio')) {
            $userData['bio'] = $request->input('bio');
        }

        if ($request->has('location')) {
            $userData['location'] = $request->input('location');
        }

        if($userData['slug'] == null){
            $userData['slug'] = str_replace(" ", "-", $userData['name']);
        }
        
        $updateOldTag = UserRepository::updateAuthor( $userId, $languageId, $blog, $blog->user_id, $role, $status, $userData);
        return response()->json($updateOldTag);
    }

    public static function deleteAuthor(Request $request)
    {
        $userId = $request->route('id');
        $languageId = $request->input('languageId');
        $deleteData = UserRepository::deleteAuthorVariant($userId, $languageId);

        return response()->json($deleteData);
    }


    /*
    * these functions are for author Variants
    */
    public static function getAuthorVariant(Request $request)
    {
        $userId =(int) $request->get('userId');
        $languageId =(int) $request->input('languageId');

        $getVariant = UserRepository::getAuthorVariant($userId, $languageId);

        return response()->json($getVariant);
    }

    public static function createAuthorVariant(Request $request) {

        $userId = $request->input('userId');
        $languageId = $request->input('languageId');
        $createVariant = UserRepository::createAuthorVariant($userId, $languageId);

        return response()->json($createVariant);
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

}
