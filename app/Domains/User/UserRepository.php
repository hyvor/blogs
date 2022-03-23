<?php

namespace App\Domains\User;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Post\PostAuthorRepository;
use App\Models\Blog;
use App\Models\User;
use App\Domains\User\Types\UserBlogOutputConsoleType;
use App\Exceptions\TrustedException;
use Exception;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    /**
     * 
     * For Hyvor users, send $hyvorUserId
     * For dummy users, send array $userData
     */
    public static function createUser(
        int $blogId,
        ?int $hyvorUserId,
        UserRoleEnum $role,
        UserStatusEnum $status = UserStatusEnum::INVITED,
        array $userData = [],
    ): User {

        if ($hyvorUserId) {
            $user = Userbase::fromId($hyvorUserId, false, true);

            if (!$user) {
                throw new TrustedException("User not found");
            }

            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'picture' => $user->picture,
                'location' => $user->location,
                'bio' => $user->bio,
                'url' => $user->url
            ];
        }

        $slug = self::findSlugForUser($blogId, $userData);

        $user = User::create([
            'blog_id' => $blogId,
            'slug' => $slug,
            'user_id' => $hyvorUserId,
            'status' => $status->value,
            'role' => $role->value,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'picture' => $userData['picture'] ?? null,
            'location' => $userData['location'] ?? null,
            'bio' => $userData['bio'] ?? null,
            'url' => $userData['url'] ?? null,
        ]);

        return $user;
    }

    public static function updateUser(int $id, array $updates) 
    {
        $user = User::find($id);
    }

    public static function deleteUser(int $id) {

        PostAuthorRepository::deleteAllWithAuthor($id);

        $user = User::find($id);

        if ($user->role === UserRoleEnum::OWNER->value) {
            throw new Exception('Owner cannot be deleted');
        }

        $user->delete();
    }

    /**
     * Get blogs of a user
     * returns an array of blogs with basic data
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

    /**
     * To sort the order displayed of blogs displayed in the console
     * $arr = [blogId, blogId] in the correct sort
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
