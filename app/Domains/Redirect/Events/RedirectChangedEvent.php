<?php

namespace App\Domains\Redirect\Events;

use App\Models\Redirect;
use Illuminate\Foundation\Events\Dispatchable;

class RedirectChangedEvent
{
    use Dispatchable;

    public function __construct(public Redirect $redirect)
    {
    }
}
