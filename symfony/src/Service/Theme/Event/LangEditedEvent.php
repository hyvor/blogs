<?php

namespace App\Service\Theme\Event;

use App\Entity\ThemeFile;

readonly class LangEditedEvent
{
    public function __construct(public ThemeFile $file) {}
}
