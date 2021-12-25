<?php
namespace App\Repositories\User;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface {

   /*  public function create(
        int $blogId, int $userId, string $userType,
        string $name, ?string $profileImage, string $type
    );

    public function update(); */

    public function getBlogs(int $userId, string $userType) : array;
    public function createBlog(int $userId, string $subdomain, string $name) : array;
    public function addUserToBlog(int $userId, string $userType, int $blogId, string $role, string $status) : User;
    public function getUserData(int $userId, string $userType) : array; /** Change to UserObject */
    public function changeSorts(int $userId, string $userType, array $arr);

}