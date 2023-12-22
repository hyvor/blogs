<?php

namespace App\Domains\Theme\Events;

use App\Models\ThemeFile;
use Illuminate\Foundation\Events\Dispatchable;

class ConfigEditedEvent
{

    use Dispatchable;

    public function __construct(public ThemeFile $file) {}

}