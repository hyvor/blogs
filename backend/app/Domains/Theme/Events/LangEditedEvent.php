<?php declare(strict_types=1);

namespace App\Domains\Theme\Events;

use App\Models\ThemeFile;
use Illuminate\Foundation\Events\Dispatchable;

class LangEditedEvent
{
    use Dispatchable;
    public function __construct(public ThemeFile $file) {}
}