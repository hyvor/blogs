<?php
namespace App\Repositories\User;

use App\Models\Blog;

class UserRepository implements UserRepositoryInterface {

    public function create(
        int $blogId, int $userId, string $userType,
        string $name, ?string $profileImage, string $type
    ) {



    }

    public function update() {
        
    }

}