<?php declare(strict_types=1);

namespace App\Domains\Redirect\Events;

use App\Models\Redirect;
use Illuminate\Foundation\Events\Dispatchable;

class DynamicRedirectChangedEvent
{
    use Dispatchable;

    public function __construct(public Redirect $redirect)
    {
    }
}