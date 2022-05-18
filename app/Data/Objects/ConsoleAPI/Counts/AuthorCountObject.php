<?php

namespace App\Data\Objects\ConsoleAPI\Counts;

use App\Models\User;

class AuthorCountObject
{

    public int $id;
    public string $name;
    public int $posts_count;

    // called from BlogCountsRepository with custom column names
    public function __construct(User $user)
    {
        $this->id = $user->id;
        $this->name = $user->name;
        $this->posts_count = $user->posts_count;
    }

}