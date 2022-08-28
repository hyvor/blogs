<?php

namespace App\Domains\Language\Events;

use App\Models\Language;
use Illuminate\Foundation\Events\Dispatchable;

class LanguageChangedEvent
{
    use Dispatchable;
    public function __construct(public Language $language) {}
}