<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Models\Redirect;

class RedirectObject
{
    public int $id;
    public int $created_at;
    public int $blog_id;
    public string $old_url;
    public string $new_url;
    public int $type; 

    public function __construct(Redirect $redirect)
    {
        $this->id = $redirect->id;
        $this->created_at = $redirect->created_at->timestamp;
        $this->blog_id = $redirect->blog_id;
        $this->old_url = $redirect->old_url;
        $this->new_url = $redirect->new_url;
        $this->type = $redirect->type;
    }
}
