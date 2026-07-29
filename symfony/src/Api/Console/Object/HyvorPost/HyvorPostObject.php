<?php

namespace App\Api\Console\Object\HyvorPost;

use App\Entity\HyvorPost;
use App\Service\Integration\HyvorPost\HyvorPostService;

class HyvorPostObject
{
    public int $id;
    public int $created_at;
    public int $newsletter_id;
    public string $embed_code;
    public bool $created_by_hyvor_blogs;

    public function __construct(HyvorPost $hyvorPost)
    {
        $this->id = $hyvorPost->getId();
        $this->created_at = $hyvorPost->getCreatedAt()->getTimestamp();
        $this->newsletter_id = $hyvorPost->getNewsletterId();
        $this->embed_code = $hyvorPost->getEmbedCode() ?? HyvorPostService::DEFAULT_EMBED_CODE;
        $this->created_by_hyvor_blogs = $hyvorPost->isCreatedByBlogs();
    }
}
