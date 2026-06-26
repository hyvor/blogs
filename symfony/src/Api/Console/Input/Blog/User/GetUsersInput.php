<?php

namespace App\Api\Console\Input\Blog\User;

class GetUsersInput
{
    public int $limit = 50;

    public int $offset = 0;

    public ?string $search = null;
}
