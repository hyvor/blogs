<?php

namespace App\Domains\User;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Media\MediaRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\Route\PermalinkRepository;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\User;
use App\Models\UserVariant;
use Exception;
use Hyvor\FilterQ\Facades\FilterQ;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

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
     * @param Blog $blog
     * @param int $limit
     * @param int $offset
     * @return Collection<User>
     */
    public static function getUsers(Blog $blog, int $limit, int $offset = 0)
    {
        $language = LanguageRepository::getPrimaryLanguage($blog);

        return User::where('blog_id', '=', $blog->id)
            ->join('user_variants', function ($join) use ($language) {
                $join
                    ->on('user_variants.user_id', '=', 'users.id')
                    ->where('user_variants.language_id', '=', $language->id);
            })
            ->orderBy('users.posts_count', 'DESC')
            ->select('users.*')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Search users by their primary language name
     *
     * @param Blog $blog
     * @param string $search
     * @param int $limit
     * @return Collection<User>
     */
    public static function searchUsers(Blog $blog, string $search, int $limit)
    {
        $search = str_replace('%', '', $search); // to make it safe
        $search .= '%';

        $primaryLanguage = LanguageRepository::getPrimaryLanguage($blog);

        return User::join(
            'user_variants',
            fn ($join) =>
                $join->on('user_variants.user_id', '=', 'users.id')
                    ->where('user_variants.language_id', '=', $primaryLanguage->id)
        )
            ->where('users.blog_id', $blog->id)
            ->where('user_variants.name', 'LIKE', $search)
            ->limit($limit)
            ->select('users.*')
            ->get();
    }

    public static function deleteUser(int $id)
    {
        PostTagAuthorRepository::deleteAllWithAuthor($id);

        $user = User::find($id);

        if ($user->role === UserRoleEnum::OWNER->value) {
            throw new Exception('Owner cannot be deleted');
        }

        $user->delete();
    }

    public static function getAuthorsWithFilterQ(
        Blog $blog,
        ?string $filter,
        int $limit,
        int $offset,
        array $orderBys = [
            ['users.posts_count', 'DESC'],
        ],
    ): CollectionWithTotal {
        $builder = FilterQ::expression($filter)
            ->builder(User::class)
            ->keys(function ($keys) {
                $keys->add('id')
                    ->column('users.id')
                    ->valueType('int');

                $keys->add('slug')
                    ->column('users.slug')
                    ->valueType('string|int')
                    ->operators('=,!=');

                $keys->add('posts_count')
                    ->column('users.posts_count')
                    ->valueType('int');

                $keys->add('created_at')
                    ->column('users.created_at')
                    ->valueType('date');
            })
            ->addWhere();

        foreach ($orderBys as $orderBy) {
            $builder->orderBy($orderBy[0], $orderBy[1]);
        }

        $tags = $builder
            ->where('users.blog_id', $blog->id)
            ->where('users.posts_count', '>', 0)
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = $builder->count();

        return new CollectionWithTotal($tags, $total);
    }

    public static function createUserFromHyvorUser(
        Blog $blog,
        int $hyvorUserId,
        UserRoleEnum $role,
        UserStatusEnum $status = UserStatusEnum::INVITED,
    ): User {
        $hyvorUser = Userbase::fromId($hyvorUserId);

        if (! $hyvorUser) {
            throw new Exception('User not found');
        }

        $pictureUrl = null;
        if ($hyvorUser->picture_url) {
            try {
                $media = MediaRepository::uploadFromUrl($blog, $hyvorUser->picture_url);
                $pictureUrl = PermalinkRepository::getMediaPermalink($media, $blog);
            } catch (UploadException) {
            }
        }

        $user = User::create([
            'blog_id' => $blog->id,
            'role' => $role,
            'status' => $status,
            'slug' => UniqueSlugGenerator::forHyvorUser($blog, $hyvorUser),
            'hyvor_user_id' => $hyvorUser->id,
            'email' => $hyvorUser->email,
            'website_url' => $hyvorUser->website_url,
            'picture_url' => $pictureUrl,
        ]);

        $language = LanguageRepository::getPrimaryLanguage($blog);

        self::createUserVariant(
            $user,
            $language,
            name: $hyvorUser->name,
            location: $hyvorUser->location,
            bio: $hyvorUser->bio
        );

        return $user;
    }

    public static function createGuestUser(
        Blog $blog,
        string $name,
    ): User {
        $user = User::create([
            'blog_id' => $blog->id,
            'role' => UserRoleEnum::CONTRIBUTOR,
            'status' => UserStatusEnum::ACTIVE,
            'slug' => UniqueSlugGenerator::forGuestUser($blog, $name),
        ]);

        $language = LanguageRepository::getPrimaryLanguage($blog);

        self::createUserVariant($user, $language, $name);

        return $user;
    }

    public static function updateUser(
        int $userId,
        int $languageId,
        $blog,
        ?int $hyvorUserId,
        UserRoleEnum $role,
        UserStatusEnum $status = UserStatusEnum::INVITED,
        array $userData = [],
    ): void {
        User::find($userId)
            ->update([
                'slug' => $userData['slug'],
                'status' => $status->value,
                'role' => $role->value,
                'email' => $userData['email'] ?? null,
                'picture_id' => $userData['pictureId'] ?? null,
                'url' => $userData['url'] ?? null,
                'social_facebook' => $userData['social_facebook'] ?? null,
                'social_twitter' => $userData['social_twitter'] ?? null,
                'social_linkedin' => $userData['social_linkedin'] ?? null,
                'social_youtube' => $userData['social_youtube'] ?? null,
                'social_instagram' => $userData['social_instagram'] ?? null,
            ]);

        UserVariant::where([
                'user_id' => $userId ,
                'language_id' => $languageId,
            ]) ->update([
                'name' => $userData['name'] ?? null,
                'location' => $userData['location'] ?? null,
                'bio' => $userData['bio'] ?? null,
            ]);
    }

    public static function deleteAuthor(int $userId, int $languageId): void
    {
        $language = Language::where('id', '=', $languageId)
        ->value('is_primary');

        if ($language == 0) {
            UserVariant::where('user_id', '=', $userId)
                ->where('language_id', '=', $languageId)
                ->delete();
        } else {
            UserVariant::where('user_id', '=', $userId)
                ->delete();

            User::find($userId)
                ->delete();
        }
    }


    public static function createUserVariant(
        User $user,
        Language $language,
        string $name,
        ?string $location = null,
        ?string $bio = null,
    ): void {
        UserVariant::create([
            'user_id' => $user->id,
            'language_id' => $language->id,
            'name' => $name,
            'location' => $location,
            'bio' => $bio,
        ]);
    }

    public static function updatePicture($blog, UploadedFile $file): bool
    {
        $media = MediaRepository::upload($blog->id, $file);
        $pictureUrl = PermalinkRepository::getMediaPermalink($media, $blog);
        // dd($pictureUrl);
        // $pictureId = $media->id;

        return User::find($blog->id)
            ->update([
                'picture_url' => $pictureUrl,
            ]);
    }

    /*
    *
    * Get blogs of a user
    * returns an array of blogs with basic data
    *
    */

    public static function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public static function getOwnerOfBlog(Blog $blog)
    {
        return User::where('blog_id', $blog->id)
            ->where('role', UserRoleEnum::OWNER)
            ->first();
    }

    public static function getUserByBlogIdAndHyvorUserId(int $blogId, int $hyvorUserId): ?User
    {
        return User::where('blog_id', $blogId)
            ->where('hyvor_user_id', $hyvorUserId)
            ->first();
    }

    public static function getUserByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug): ?User
    {
        $user = User::where('blog_id', $blogId);
        if ($id) {
            $user->where('id', $id);
        } else {
            $user->where('slug', $slug);
        }

        return $user->first();
    }

    public static function getUserByBlogIdAndSlug(int $blogId, string $slug): ?User
    {
        return self::getUserByBlogIdAndIdentifier($blogId, null, $slug);
    }
}
