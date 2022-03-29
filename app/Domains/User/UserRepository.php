<?php

namespace App\Domains\User;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Post\PostAuthorRepository;
use App\Models\User;
use App\Models\UsersVariant;
use App\Exceptions\TrustedException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Domains\Language\LanguageRepository;
use App\Models\Language;


use Hyvor\HyvorConnecter\Userbase;
use App\Domains\User\Types\UserBlogOutputConsoleType;
use App\Models\Blog;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;


/**
 * 
 * There are two user types: 
 *  - Hyvor users
 *  - non-Hyvor users (dummy users)
 * 
 * For dummy users, user_id is null
 */
class UserRepository
{
    public static function deleteUser(int $id) {

        PostAuthorRepository::deleteAllWithAuthor($id);

        $user = User::find($id); 

        if ($user->role === UserRoleEnum::OWNER->value) {
            throw new Exception('Owner cannot be deleted');
        }

        $user->delete();
    }

    public static function getAuthor($blog)
    {
        $language = LanguageRepository::getPrimaryLanguage($blog);

        $users = User::where('users.blog_id', '=', $blog->id)
        ->join('users_variants', function($join) use ($language) {
            $join->on('users_variants.user_id', '=', 'users.id');
            $join->where('users_variants.language_id', '=',  $language->id);
        })
        ->latest()
        ->get();

        return $users;
    }

    /**
    * 
    * For Hyvor users, send $hyvorUserId
    * For dummy users, send array $userData
    */
    public static function createUser(
        $blog,
        ?int $hyvorUserId,
        UserRoleEnum $role,
        UserStatusEnum $status = UserStatusEnum::INVITED,
        array $userData = [],
    // ): User {
    ) {

        // This is the place where I got the ( cURL error 6: Could not resolve host: api ) Error so I had to comment it.
        // if ($hyvorUserId) {
        //     $user = Userbase::fromId($hyvorUserId, false, true);

        //     if (!$user) {
        //         throw new TrustedException("User not found");
        //     }

        //     $userData = [
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'picture' => $user->picture,
        //         'location' => $user->location,
        //         'bio' => $user->bio,
        //         'url' => $user->url
        //     ];
        // }

        // $slug = self::findSlugForUser($blogId, $userData);

        $user = User::create([
            'blog_id' => $blog->id,
            'slug' => $userData['slug'],
            // 'user_id' => $hyvorUserId,
            'user_id' => 2,
            'status' => $status->value,
            'role' => $role->value,
            'email' => $userData['email'],
            'picture' => $userData['picture'] ?? null,
            'url' => $userData['url'] ?? null,
            'social_facebook' => $userData['social_facebook'] ?? null,
            'social_twitter' => $userData['social_twitter'] ?? null,
            'social_linkedin' => $userData['social_linkedin'] ?? null,
            'social_youtube' => $userData['social_youtube'] ?? null,
            'social_instagram' => $userData['social_instagram'] ?? null,
        ]);

        $getLanguage = $blog->languages()->where('is_primary', true)->first();

        UsersVariant::create([
            'user_id' => $user->id,
            'language_id' => $getLanguage->id,
            'name' => $userData['name'],
            'location' => $userData['location'] ?? null,
            'bio' => $userData['bio'] ?? null,
        ]);

        // return $user;
    }

    public static function updateAuthor( 
        int $userId, 
        int $languageId,
        $blog,
        ?int $hyvorUserId,
        UserRoleEnum $role,
        UserStatusEnum $status = UserStatusEnum::INVITED,
        array $userData = [],
    ){

        User::find($userId)
            ->update([
                'slug' => $userData['slug'],
                'status' => $status->value,
                'role' => $role->value,
                'email' => $userData['email'] ?? null,
                'picture' => $userData['picture'] ?? null,
                'url' => $userData['url'] ?? null,
                'social_facebook' => $userData['social_facebook'] ?? null,
                'social_twitter' => $userData['social_twitter'] ?? null,
                'social_linkedin' => $userData['social_linkedin'] ?? null,
                'social_youtube' => $userData['social_youtube'] ?? null,
                'social_instagram' => $userData['social_instagram'] ?? null,
            ]);

        UsersVariant::where([
                'user_id' => $userId ,
                'language_id'=> $languageId,
            ]) ->update([
                'name' => $userData['name'] ?? null,
                'location' => $userData['location']  ?? null,
                'bio' => $userData['bio']  ?? null
            ]);
    }

    // This function is used to delete users according to specific conditions.
    public static function deleteAuthorVariant( int $userId, int $languageId)
    {
        $language = Language::where('id','=', $languageId)
        ->value('is_primary');

        if($language == 0){
            UsersVariant::where('user_id','=',$userId)
                ->where('language_id','=',$languageId)
                ->delete();
        }
        else{
            UsersVariant::where('user_id','=',$userId)
                ->delete();

            User::find($userId)
                ->delete();
        }
    }
        
    /*
    *
    * these functions are for author Variants
    *
    */
    public static function getAuthorVariant($userId, $languageId)
    {
        $users = UsersVariant::where('user_id', '=', $userId)
        ->where('language_id', '=', $languageId)
        ->get();

        return $users;
    }

    // This function is used to create a variant if it doesn'texist.'
    public static function createAuthorVariant($userId, $languageId) 
    {
        $language = Language::where('id','=', $languageId)
        ->value('is_primary');

        if($language == 0){
            $userVariantCheck = UsersVariant::where('user_id','=', $userId)
            ->where('language_id','=', $languageId)
            ->first();

            if($userVariantCheck == null){
                UsersVariant::create([
                    'user_id' => $userId,
                    'language_id' => $languageId,
                ]);
            }
        }
    }



    /*
    *
    * Get blogs of a user
    * returns an array of blogs with basic data
    *
    */
    public static function getBlogsOfUser(int $hyvorUserId): Collection
    {
        return User::where('user_id', $hyvorUserId)
            ->where('status', 'active')
            ->orderBy('sort', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->with('blog', 'blog.subscriptions')
            ->get();
    }

    public static function getUser(int $id) : ?User {
        return User::find($id);
    }

    public static function getUserByBlogIdAndHyvorUserId(int $blogId, int $hyvorUserId) : ?User 
    {
        return User::where('blog_id', $blogId)
            ->where('user_id', $hyvorUserId)
            ->first();
    }

    public static function getUserByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug) : ?User
    {
        $user = User::where('blog_id', $blogId);
        if ($id) {
            $user->where('id', $id);
        } else {
            $user->where('slug', $slug);
        }
        return $user->first();
    }

    public static function getUserByBlogIdAndSlug(int $blogId, string $slug) : ?User
    {
        return self::getUserByBlogIdAndIdentifier($blogId, null, $slug);
    }

    /*
    *
    * To sort the order displayed of blogs displayed in the console
    * $arr = [blogId, blogId] in the correct sort
    *
    */
    public static function changeBlogSorts(int $userId, array $arr) : void
    {
        $i = 1;
        foreach ($arr as $blogId) {
            User::where('blog_id', $blogId)
                ->where('user_id', $userId)
                ->update([
                    'sort' => $i
                ]);
            $i++;
        }
    }


    /**
     * Helpers
     * =================================================================================================
     */

    /**
     * This function finds a unique slug for the user when creating new profile
     * First check name and email
     * If that doesn't work, use a random string
     * It should work almost every time.
     */
    private static function findSlugForUser(int $blogId, array $userData) 
    {

        $checks = [
            $userData['name'],
            $userData['email'],
            Str::random()
        ];

        foreach ($checks as $check) {

            $slug = Str::slug($check);

            if (!
                User::where('blog_id', $blogId)
                    ->where('slug', $slug)
                    ->exists()
            ) {
                return $slug;
            }

        }

        throw new TrustedException('Unable to find a slug for the user');
    }
}
