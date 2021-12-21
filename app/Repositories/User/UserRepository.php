<?php
namespace App\Repositories\User;

use App\Models\Blog;
use App\Models\User;

class UserRepository implements UserRepositoryInterface {

    public function create(
        int $blogId, int $userId, string $userType,
        string $name, ?string $profileImage, string $type
    ) {



    }

    public function update() {
        
    }


    /**
     * Get blogs of a user
     * returns an array of User with blog relation and its subscriptionS
     * for console data
     */
    public function getBlogs(int $userId, string $userType) {

        return User::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('status', 'active')
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->select('role', 'blog_id')
            ->with('blog:id,name,subdomain', 'blog.subscriptions')
            ->get();

    } 

}