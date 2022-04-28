<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Models\User;

class UserBlogUserObject
{
    public int $id;
    public string $role;

    public function __construct(User $user)
    {
        $this->id = $user->id;
        $this->role = $user->role;
    }
}
