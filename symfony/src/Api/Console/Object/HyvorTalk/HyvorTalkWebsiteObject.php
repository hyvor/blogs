<?php

namespace App\Api\Console\Object\HyvorTalk;

use App\Entity\HyvorTalkWebsite;
use App\Service\Integration\HyvorTalk\HyvorTalkService;

class HyvorTalkWebsiteObject
{

    public int $id;
    public int $created_at;
    public int $website_id;
    public string $embed_code;
    public bool $created_by_hyvor_blogs;
    public string $embed_default_code;

    public function __construct(HyvorTalkWebsite $website)
    {
        $this->id = $website->getId();
        $this->created_at = $website->getCreatedAt()->getTimestamp();
        $this->website_id = $website->getWebsiteId();
        $this->embed_code = HyvorTalkService::getEmbedCode($website);
        $this->created_by_hyvor_blogs = $website->isCreatedByBlogs();
        $this->embed_default_code = HyvorTalkService::getDefaultEmbedCode($website->getWebsiteId());
    }

}
