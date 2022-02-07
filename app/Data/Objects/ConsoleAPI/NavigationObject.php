<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Navigation;

class NavigationObject
{
    public int $id;
    public int $uploaded_at;
    public int $blog_id;
    public string $navigation_name;
    public string $navigation_url;
    public string $type; 

    public function __construct(Navigation $Navigation)
    {
        $this->id = $Navigation->id;
        $this->uploaded_at = $Navigation->created_at->timestamp;
        $this->blog_id = $Navigation->blog_id;
        $this->navigation_name = $Navigation->navigation_name;
        $this->navigation_url = $Navigation->navigation_url;
        $this->type = $Navigation->type;
    }
}
