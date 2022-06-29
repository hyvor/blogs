<?php

namespace App\Domains\User;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Media\Exceptions\UploadException;
use App\Domains\Media\MediaRepository;
use App\Domains\Post\PostTagAuthorRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Domains\User\Events\UserVariantCreatedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Domains\User\Events\UserVariantUpdatedEvent;
use App\Domains\User\Mail\InviteUserMail;
use App\Helpers\CollectionWithTotal;
use App\Models\Blog;
use App\Models\Language;
use App\Models\User;
use App\Models\UserVariant;
use Exception;
use Hyvor\FilterQ\Facades\FilterQ;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

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
            ->orderByRaw("users.role='owner' DESC")
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
            ->select('users.*')
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

        $user = $user->refresh();

        UserCreatedEvent::dispatch($user);

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

        $user = $user->refresh();

        UserCreatedEvent::dispatch($user);

        return $user;
    }

    public static function updateUser(
        User $user,
        array $updates,
    ): User {
        foreach ($updates as $key => $value) {
            $user->$key = $value;
        }

        $user->save();

        UserUpdatedEvent::dispatch($user);

        return $user;
    }

    public static function deleteUser(User $user): void
    {

        // variants
        $user->variants->map(fn ($variant) => self::deleteUserVariant($variant));

        // post-authors
        PostTagAuthorRepository::deletePostAuthorsByUser($user);

        $user->delete();

        UserDeletedEvent::dispatch($user);
    }

    public static function createUserVariant(
        User $user,
        Language $language,
        ?string $name = null,
        ?string $location = null,
        ?string $bio = null,
    ): UserVariant {
        $variant = UserVariant::create([
            'user_id' => $user->id,
            'language_id' => $language->id,
            'name' => $name,
            'location' => $location,
            'bio' => $bio,
        ]);

        UserVariantCreatedEvent::dispatch($variant->refresh());

        return $variant;
    }

    public static function updateUserVariant(UserVariant $variant, array $updates): UserVariant
    {
        foreach ($updates as $key => $value) {
            $variant->$key = $value;
        }
        $variant->save();

        UserVariantUpdatedEvent::dispatch($variant);

        return $variant;
    }

    public static function deleteUserVariant(UserVariant $variant)
    {
        $variant->delete();

        UserVariantDeletedEvent::dispatch($variant);
    }


    // getters
    public static function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public static function getOwnerOfBlog(Blog $blog) : User
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

    public static function getUserVariantByUserIdAndLanguageId(int $userId, int $languageId): ?UserVariant
    {
        return UserVariant::where('language_id', $languageId)
            ->where('user_id', $userId)
            ->first();
    }

    public static function sendInviteEmail(User $user)
    {
        $hyvorUser = Userbase::fromId($user->hyvor_user_id, true);

        Mail::to($hyvorUser->email)->send(new InviteUserMail($user, $hyvorUser));
    }

    public static function activateUser(User $user)
    {
        $user->status = UserStatusEnum::ACTIVE;
        $user->save();
    }
}
