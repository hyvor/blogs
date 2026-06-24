<?php

namespace App\Api\Console\Input\Blog\User;

use Symfony\Component\Validator\Constraints as Assert;

class SearchUsersInput
{
    #[Assert\NotBlank]
    public string $search;
}
