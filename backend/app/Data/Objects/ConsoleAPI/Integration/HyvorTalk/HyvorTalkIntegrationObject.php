<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Integration\HyvorTalk;

use App\Models\HyvorTalkWebsite;

class HyvorTalkIntegrationObject
{

    public int $id;
    public int $created_at;
    public int $website_id;

    public function __construct(HyvorTalkWebsite $website)
    {
        $this->id = $website->id;
        $this->created_at = $website->created_at->getTimestamp();
        $this->website_id = $website->website_id;
    }

}