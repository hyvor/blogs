<?php

namespace App\Domains\Media\Events;

use App\Models\Media;
use Illuminate\Foundation\Events\Dispatchable;

class MediaDeletedEvent
{
    use Dispatchable;

    public function __construct(public Media $media)
    {
    }
}
