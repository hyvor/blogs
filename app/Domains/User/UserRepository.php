<?php

namespace App\Domains\User;

use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Models\Blog;
use App\Models\User;
use App\Domains\User\Types\UserBlogOutputConsoleType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserRepository
{
    /**
     * Get blogs of a user
     * returns an array of blogs with basic data
     */
    public static function getBlogsOfUser(int $hyvorUserId,): Collection
    {
        return User::where('user_id', $hyvorUserId)
            ->where('status', 'active')
            ->orderBy('sort', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->with('blog', 'blog.subscriptions')
            ->get()
            ->map(function ($user) {
                return new UserBlogObject($user);
            });
    }

    public static function getTagByBlogIdAndIdentifier(int $blogId, ?int $id, ?string $slug)
    {
        $post = User::where('blog_id', $blogId);
        if ($id) {
            $post->where('id', $id);
        } else {
            $post->where('slug', $slug);
        }
        return $post->first();
    }

    public function createBlog(int $userId, string $subdomain, string $name): array
    {

        $blog = Blog::create([
            'user_id' => $userId,
            'subdomain' => $subdomain,
            'name' => $name
        ]);

        $user = $this->addUserToBlog($userId, 'hyvor', $blog->id, 'owner', 'active');

        return $this->convertUserToBlogForConsole($user);
    }

    /**
     * The console only need some data of the Blog object
     */
    private function convertUserToBlogForConsole(User $user)
    {
        $plan = null;
        foreach ($user->blog->subscriptions as $sub) {
            if ($sub->valid()) {
                $plan = $this->subscriptionRepo->getPlanNameByPlanId($sub->paddle_plan);
            }
        }

        return [
            'id' => $user->blog_id,
            'role' => $user->role,
            'name' => $user->blog->name,
            'subdomain' => $user->blog->subdomain,
            'plan' => $plan,
            'posts_count' => $user->blog->posts_count
        ];
    }

    /**
     *
     * To sort the order displayed of blogs displayed in the console
     * $arr = [blogId, blogId] in the correct sort
     */
    public function changeSorts(int $userId, string $userType, array $arr)
    {
        $i = 1;
        foreach ($arr as $blogId) {
            User::where('blog_id', $blogId)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->update([
                    'sort' => $i
                ]);
            $i++;
        }
    }

    public function getUsers(blog $blog)
    {
        $users = User::where('blog_id', $blog->id)
            ->get();
    }

    public function addUserToBlog(
        int $userId,
        string $userType,
        int $blogId,
        string $role,
        string $status = 'invited'
    ): User {

        $userData = $this->getUserData($userId, $userType);

        $user = User::create([
            'blog_id' => $blogId,
            'user_id' => $userId,
            'user_type' => $userType,
            'status' => $status,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'slug' => Str::slug($userData['name'])
        ]);

        return $user;
    }

    public function getUserData($userId, $userType): array
    {

        return [
            'name' => 'Supun',
            'email' => 'supun@hyvor.com'
        ];
    }
}
