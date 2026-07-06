<?php

namespace App\Api\Console\Input\Post;

use Symfony\Component\Validator\Constraints as Assert;

class PublishPostVariantInput
{
    #[Assert\NotNull]
    public int $language_id;
}
