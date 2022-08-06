<?php

namespace App\Domains\Media\Events;

use App\Models\Media;
use Illuminate\Foundation\Events\Dispatchable;

class MediaCreatedEvent
{
    use Dispatchable;

    public function __construct(public Media $media) {}
}