<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;

use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Domains\User\Events\UserCreatedEvent;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;

use App\Models\Blog;
use App\Models\User;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleUserController extends Controller
{
    public static function get(Request $request, Blog $blog)
    {
        $request->validate([
            'offset' => 'integer',
        ]);

        $limit = 50;
        $offset = $request->input('offset', 0);

        $users = UserRepository::getUsers($blog, $limit, $offset)->map(fn ($user) => new UserObject($user, $blog));

        return response()->json($users);
    }

    public static function search(Request $request, Blog $blog)
    {
        $request->validate([
            'search' => 'required|string',
        ]);

        $search = $request->input('search');

        $users = UserRepository::searchUsers($blog, $search, limit: 10)
            ->map(fn ($user) => new UserObject($user, $blog));

        return response()->json($users);
    }

    public static function create(Request $request, Blog $blog)
    {

        $request->validate([
            'username_or_email' => 'required|string',
            'role' => ['required', new Enum(UserRoleEnum::class)],
        ]);

        $usernameOrEmail = $request->input('username_or_email');
        $role = UserRoleEnum::from($request->input('role'));

        if (str_contains($usernameOrEmail, '@')) {
            $hyvorUser = Userbase::fromEmail($usernameOrEmail);
        } else {
            $hyvorUser = Userbase::fromUsername($usernameOrEmail);
        }

        if (!$hyvorUser) {
            throw new TrustedException('Unable to find the user');
        }

        if ($role === UserRoleEnum::OWNER) {
            throw new TrustedException('Owners cannot be created. Use ownership transferring');
        }

        if (UserRepository::getUserByBlogIdAndHyvorUserId($blog->id, $hyvorUser->id)) {
            throw new TrustedException('User already exists');
        }

        $user = UserRepository::createUserFromHyvorUser($blog, $hyvorUser->id, $role);

        return response()->json(new UserObject($user, $blog));
    }

    public static function createGuest(Request $request, Blog $blog)
    {

        $request->validate([
            'name' => 'required|string'
        ]);

        $name = $request->input('name');

        $user = UserRepository::createGuestUser($blog, $name);

        return response()->json(new UserObject($user, $blog));

    }

    public static function update(Request $request, User $user, Blog $blog)
    {

        $validators = [
            'hyvor_user_id' => 'integer|nullable',
            'role' => new Enum(UserRoleEnum::class),
            'status' => 'string|in:active,blocked',
            'slug' => 'string',
            'email' => 'string|nullable',
            'website_url' => 'string|nullable',
            'picture_url' => 'string|nullable',

            'social_facebook' => 'string|nullable',
            'social_twitter' => 'string|nullable',
            'social_linkedin' => 'string|nullable',
            'social_youtube' => 'string|nullable',
            'social_tiktok' => 'string|nullable',
            'social_instagram' => 'string|nullable',
            'social_github' => 'string|nullable',
        ];

        $request->validate($validators);

        $updatables = array_keys($validators);
        $updates = [];

        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $input = $request->input($updatable);

                $input = match ($updatable) {
                    'role' => UserRoleEnum::from($input),
                    'status' => UserStatusEnum::from($input),
                    default => $input
                };

                $updates[$updatable] = $input;
            }
        }

        if (isset($updates['role'])) {
            $role = $updates['role'];

            if ($role === UserRoleEnum::OWNER) {
                throw new TrustedException('You cannot update the role to owner. Use transferring instead');
            }
            if ($user->role === UserRoleEnum::OWNER) {
                throw new TrustedException('You cannot update the role of the owner. Use transferring instead');
            }
        }

        if (isset($updates['status']) && $user->role === UserRoleEnum::OWNER) {
            throw new TrustedException('You cannot update the status of the owner');
        }

        if (count($updates) > 0) {
            UserRepository::updateUser($user, $updates);
        }

        $user->refresh();

        return response()->json(new UserObject($user));

    }

    public static function delete(User $user)
    {
        UserRepository::deleteUser($user);
        return response()->json();
    }

    public static function createVariant(Request $request)
    {
        $userId = $request->input('userId');
        $languageId = $request->input('languageId');
        $createVariant = UserRepository::createAuthorVariant($userId, $languageId);

        return response()->json($createVariant);
    }

}
