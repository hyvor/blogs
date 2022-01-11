<?php
namespace App\Domains\User\Types;

use App\Models\Blog;
use App\Models\User;

/**
 *  
 * This is used in the console to represent a user's blog.
 * It contains user's and blog's data that is essential to render the console UI
 * User can be of any role.
 * 
 */

class UserBlogOutputConsoleType {

    public _BlogType $blog;
    public _UserType $user;

    /**
     * @var User $user should be fetched with the following relations
     * 'blog', 'blog.subscriptions'
     * If not fetched with those relations, this function will create new queries to fetch them
     */
    public function __construct(User $user) {
        $this->blog = new _BlogType($user->blog);
        $this->user = new _UserType($user);
    }

}

class _BlogType {

    public int $id;
    public string $name;
    public string $subdomain;
    public ?string $plan;
    public int $posts_count;
    public int $users_count = 200;

    public function __construct(Blog $blog) {
        $plan = null;
        foreach ($blog->subscriptions as $sub) {
            if ($sub->valid()) {
                $plan = $sub; // $this->subscriptionRepo->getPlanNameByPlanId($sub->paddle_plan);
            }
        }

        $this->id = $blog->id;
        $this->name = $blog->name;
        $this->subdomain = $blog->subdomain;
        $this->plan = $plan;
        $this->posts_count = $blog->posts_count;
    }

}

class _UserType {

    public int $id;
    public string $role;

    public function __construct(User $user) {

        $this->id = $user->id;
        $this->role = $user->role;

    }

}
