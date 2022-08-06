<?php

namespace App\Domains\Theme\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;

class AssetEditedEvent
{

    use Dispatchable;

    public function __construct(public Blog $blog, public string $name) {}

}