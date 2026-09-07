<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\RedirectType;
use App\Entity\Redirect;

class RedirectObject
{
    public int $id;
    public int $created_at;
    public bool $dynamic;
    public string $path;
    public string $to;
    public RedirectType $type;

    public function __construct(Redirect $redirect)
    {
        $this->id = $redirect->getId();
        $this->created_at = $redirect->getCreatedAt()->getTimestamp();
        $this->dynamic = $redirect->isDynamic();
        $this->path = $redirect->getPath();
        $this->to = $redirect->getTo();
        $this->type = $redirect->getType();
    }
}
