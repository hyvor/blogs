<?php

namespace App\Data\Objects\ConsoleAPI\Integration\HyvorTalk;

use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;

class GatedContentRuleObject
{

    public int $id;
    public ?TagObject $tag;
    public ?string $minimum_plan;
    public ?string $gate;

    public function __construct(HyvorTalkGatedContentRule $gatedContent, Blog $blog)
    {
        $this->id = $gatedContent->id;
        $this->tag = $gatedContent->tag ? new TagObject($gatedContent->tag, $blog) : null;
        $this->minimum_plan = $gatedContent->minimum_plan;
        $this->gate = $gatedContent->gate;
    }

}