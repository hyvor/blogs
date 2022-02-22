<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Navigation;

class NavigationObject
{
    public int $id;
    public int $uploaded_at;
    public int $blog_id;
    public string $name;
    public string $url;
    public string $type; 
    public string $itemNumber; 

    public function __construct(Navigation $Navigation)
    {
        $this->id = $Navigation->id;
        $this->uploaded_at = $Navigation->created_at->timestamp;
        $this->blog_id = $Navigation->blog_id;
        $this->name = $Navigation->name;
        $this->url = $Navigation->url;
        $this->type = $Navigation->type;
        $this->itemNumber = $Navigation->itemNumber;
    }
}
