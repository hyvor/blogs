<?php

namespace App\Api\Console\Object\HyvorTalk;

use App\Entity\HyvorTalkWebsite;

class HyvorTalkWebsiteObject
{

    public int $id;
    public int $created_at;
    public int $website_id;

    public function __construct(HyvorTalkWebsite $website)
    {
        $this->id = $website->getId();
        $this->created_at = $website->getCreatedAt()->getTimestamp();
        $this->website_id = $website->getWebsiteId();
    }

}
