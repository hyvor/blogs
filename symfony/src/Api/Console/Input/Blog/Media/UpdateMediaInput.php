<?php

namespace App\Api\Console\Input\Blog\Media;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateMediaInput
{
    #[Assert\NotBlank]
    public string $name;
}
