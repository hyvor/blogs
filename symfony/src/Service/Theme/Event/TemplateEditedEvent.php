<?php

namespace App\Service\Theme\Event;

use App\Entity\ThemeFile;

readonly class TemplateEditedEvent
{
    public function __construct(public ThemeFile $file) {}
}
