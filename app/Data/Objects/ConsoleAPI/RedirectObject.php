<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Redirect;

class RedirectObject
{
    public int $id;
    public int $created_at;
    public int $blog_id;
    public string $path;
    public string $to;
    public int $type; 

    public function __construct(Redirect $redirect)
    {
        $this->id = $redirect->id;
        $this->created_at = $redirect->created_at->timestamp;
        $this->blog_id = $redirect->blog_id;
        $this->path = $redirect->path;
        $this->to = $redirect->to;
        $this->type = $redirect->type;
    }
}
