<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Data\Enums\UserRoleEnum;
use App\Models\User;

class UserBlogUserObject
{
    public int $id;
    public UserRoleEnum $role;

    public function __construct(User $user)
    {
        $this->id = $user->id;
        $this->role = $user->role;
    }
}
