<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;

use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Data\Objects\ConsoleAPI\User\UserVariantObject;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;

use App\Models\Blog;
use App\Models\Language;
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

        UserRepository::sendInviteEmail($user);

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

        return response()->json(new UserObject($user, $blog));

    }

    public static function delete(User $user)
    {
        UserRepository::deleteUser($user);
        return response()->json();
    }

    public static function createVariant(Blog $blog, User $user, Language $language)
    {
        $variant = UserRepository::createUserVariant($user, $language);
        return response()->json(new UserVariantObject($variant, $user, $blog));
    }

    public static function updateVariant(Request $request, Blog $blog, User $user, Language $language)
    {

        $variant = UserRepository::getUserVariantByUserIdAndLanguageId($user->id, $language->id);

        if (!$variant)
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);

        $validations = [
            'name' => 'string|nullable',
            'bio' => 'string|nullable',
            'location' => 'string|nullable'
        ];

        $request->validate($validations);

        $updatables = array_keys($validations);

        $updates = [];
        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $updates[$updatable] = $request->input($updatable);
            }
        }

        $variant = UserRepository::updateUserVariant($variant, $updates);

        return response()->json(new UserVariantObject($variant, $user, $blog));
    }

    public static function deleteVariant(User $user, Language $language)
    {

        if ($language->is_primary) {
            throw new TrustedException(
                'Primary language variant cannot be deleted. Delete the user instead',
                TrustedException::ERROR_UNPROCESSABLE
            );
        }

        $variant = UserRepository::getUserVariantByUserIdAndLanguageId($user->id, $language->id);

        if (!$variant)
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);

        UserRepository::deleteUserVariant($variant);

        return response()->json();

    }

    public static function acceptInvite(Request $request)
    {

        $request->validate([
            'user_id' => 'required|integer',
            'signature' => 'required|string'
        ]);

        if (!$request->hasValidSignature()) {
            return response()->view('confirmation', [
                'type' => 'error',
                'title' => 'Invalid Link',
                'description' => 'Unable to accept the invitation. The link may be altered or expired. Please ask the admins of the blog to re-invite.'
            ], 422);
        }

        $userId = $request->input('user_id');
        $user = UserRepository::getUserById($userId);

        UserRepository::activateUser($user);

        return view('confirmation', [
            'title' => 'Invitation Accepted',
            'description' => 'You have accepted the invitation to join the blog. You can visit the <a class="link" href="https://blogs.hyvor.com/console">Hyvor Blogs Console</a> to access all your blogs.'
        ]);

    }

    public function resendInvite(User $user)
    {

        if ($user->status !== UserStatusEnum::INVITED) {
            throw new TrustedException('User is not invited');
        }

        UserRepository::sendInviteEmail($user);

        return response()->json();
    }

}
