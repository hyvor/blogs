<?php

namespace App\Domains\Theme\Events;

use App\Models\ThemeFile;
use Illuminate\Foundation\Events\Dispatchable;

class TemplateEditedEvent
{
    use Dispatchable;

    public function __construct(public ThemeFile $file) {}
}