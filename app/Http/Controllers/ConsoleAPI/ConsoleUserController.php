<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;

use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;

use App\Models\Blog;
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

    public static function updateAuthor(Request $request, Blog $blog)
    {



    }

    public static function deleteAuthor(Request $request)
    {
        $userId = $request->route('id');
        $languageId = $request->input('languageId');
        $deleteData = UserRepository::deleteAuthor($userId, $languageId);

        return response()->json($deleteData);
    }

    public static function createUserVariant(Request $request)
    {
        $userId = $request->input('userId');
        $languageId = $request->input('languageId');
        $createVariant = UserRepository::createAuthorVariant($userId, $languageId);

        return response()->json($createVariant);
    }

}
