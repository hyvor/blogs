<?php

namespace App\Event;

use App\Entity\Language;

readonly class LanguageChangedEvent
{
    public function __construct(public Language $language) {}
}
