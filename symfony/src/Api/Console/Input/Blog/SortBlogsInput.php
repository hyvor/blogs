<?php

namespace App\Api\Console\Input\Blog;

use Symfony\Component\Validator\Constraints as Assert;

class SortBlogsInput
{

    /**
     * @var list<int>
     */
    #[Assert\NotBlank]
    #[Assert\All(new Assert\Type('int'))]
    public array $blog_ids;

}
