<?php

namespace App\Service\Redirect\Event;

use App\Entity\Redirect;

readonly class RedirectChangedEvent
{
    public function __construct(public Redirect $redirect) {}
}
