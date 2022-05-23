<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;

use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;

use App\Models\Blog;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\HyvorConnecter\User;
use Illuminate\Http\Request;
use App\Exceptions\TrustedException;

class ConsoleUserController extends Controller
{

    public static function getUsers(Request $request, Blog $blog)
    {
        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
        ]);

       $limit = $request->input('limit') ?? 50;
       $offset = $request->input('offset') ?? 0;

        $Users = UserRepository::getUsers($blog, $limit, $offset)
                ->map(function ($users) use ($blog) {
                    return new UserObject($users, $blog);
                });

        return response()->json($Users);
    }

    public static function searchUsers(Request $request, Blog $blog)
    {

        $request->validate([
            'search' => 'required|string'
        ]);

        $search = $request->input('search');

        $users = UserRepository::searchUsers($blog, $search, limit: 10)
            ->map(fn ($user) => new UserObject($user, $blog));

        return response()->json($users);

    }

    public static function createUser(Request $request, Blog $blog)
    {
        $role = UserRoleEnum::from($request->input('role'));
        $status = UserStatusEnum::from($request->input('status'));

        if ($request->has('slug')) {
            $userData['slug'] = $request->input('slug');
        }
        if ($request->has('email')) {
            $userData['email'] = $request->input('email');
        }

        if ($request->has('pictureUrl')) {
            $userData['pictureUrl'] = $request->input('pictureUrl');
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

        if ($userData['slug'] == null) {
            $userData['slug'] = str_replace(" ", "-", $userData['name']);
        }

        $currentUser = UserRepository::getUserByBlogIdAndSlug($blog->id, $userData['slug']);
        
        if ($currentUser) {
            throw new UserRepository('Slug already exists');
        }

        $hyvorUserId = null;

        // I changed here from user_id to hyvor_user_id
        $createUser = UserRepository::createUser($blog, $hyvorUserId, $role, $status, $userData);

        return response()->json($createUser);
    }

    public static function updateUser(Request $request, Blog $blog)
    {
        $userId = $request->route('id');
        $languageId = $request->input('languageId');
        $role = UserRoleEnum::from($request->input('role'));
        $status = UserStatusEnum::from($request->input('status'));

        if ($request->has('slug')) {
            $userData['slug'] = $request->input('slug');
        }
        if ($request->has('email')) {
            $userData['email'] = $request->input('email');
        }
        if ($request->has('pictureUrl')) {
            $userData['pictureUrl'] = $request->input('pictureUrl');
        }
        if ($request->has('websiteUrl')) {
            $userData['websiteUrl'] = $request->input('websiteUrl');
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

        if ($userData['slug'] == null) {
            $userData['slug'] = str_replace(" ", "-", $userData['name']);
        }

        $currentUser = UserRepository::getUserByBlogIdAndSlug($blog->id, $userData['slug']);
        
        if ($currentUser) {
            throw new TrustedException('Slug already exists');
        }
        

        // I changed here from user_id to hyvor_user_id
        $updateUser = UserRepository::updateUser($userId, $languageId, $role, $status, $userData);

        return response()->json($updateUser);
    }

    public static function deleteUser(Request $request)
    {
        $userId = $request->route('id');
        $languageId = $request->input('languageId');
        $deleteData = UserRepository::deleteAuthor($userId, $languageId);

        return response()->json($deleteData);
    }


    /*
    *
    * these functions are for author Variants
    *
    */
    public static function createUserVariant(Request $request)
    {
        $userId = $request->input('userId');
        $languageId = $request->input('languageId');
        $createVariant = UserRepository::createAuthorVariant($userId, $languageId);

        return response()->json($createVariant);
    }

    public static function updatePicture(Request $request, Blog $blog)
    {
        $file = $request->file('file');
        // $request->validate([
        //     'file' => 'required|file'
        // ]);

        $icon = UserRepository::updatePicture($blog, $file);

        return response()->json($icon);
    }



}
