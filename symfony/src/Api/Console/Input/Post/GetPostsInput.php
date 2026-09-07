<?php

namespace App\Api\Console\Input\Post;

use Symfony\Component\Validator\Constraints as Assert;

class GetPostsInput
{
    #[Assert\Choice(choices: ['featured', 'published', 'draft', 'scheduled'])]
    public ?string $status = null;

    public ?int $author_id = null;

    public ?int $tag_id = null;

    public ?int $start_timestamp = null;

    public ?int $end_timestamp = null;

    public ?string $search = null;

    public ?int $language_id = null;

    #[Assert\Range(max: 100)]
    public int $limit = 50;

    public int $offset = 0;
}
