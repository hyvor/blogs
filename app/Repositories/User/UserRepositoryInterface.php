<?php
namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface {

    public function create(
        int $blogId, int $userId, string $userType,
        string $name, ?string $profileImage, string $type
    );

    public function update();

}