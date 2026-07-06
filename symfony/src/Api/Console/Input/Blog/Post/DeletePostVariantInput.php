<?php

namespace App\Api\Console\Input\Blog\Post;

use Symfony\Component\Validator\Constraints as Assert;

class DeletePostVariantInput
{
    #[Assert\NotNull]
    public int $language_id;
}
