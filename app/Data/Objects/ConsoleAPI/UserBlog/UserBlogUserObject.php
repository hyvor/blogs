<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Models\User;

class UserBlogUserObject
{
    public int $id;
    public string $role;
    public $posts_count;

    public function __construct(User $user)
    {

        $this->id = $user->id;
        $this->role = $user->role;
        $this->posts_count = 0;// $user->posts_count;
    }
}
