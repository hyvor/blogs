<?php

namespace App\Data\Objects\ConsoleAPI;

use App\Data\Enums\RedirectTypeEnum;
use App\Models\Redirect;

class RedirectObject
{
    public int $id;

    public int $created_at;

    public string $path;

    public string $to;

    public RedirectTypeEnum $type;

    public function __construct(Redirect $redirect)
    {
        $this->id = $redirect->id;
        $this->created_at = $redirect->created_at->getTimestamp();
        $this->path = $redirect->path;
        $this->to = $redirect->to;
        $this->type = $redirect->type;
    }
}
