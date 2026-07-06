<?php

namespace App\Entity\Enum;

enum BlogType: string
{
    case DEFAULT = 'default';
    case DEV = 'dev';
    case PREVIEW = 'preview';

    public function isNonDefault(): bool
    {
        return $this !== self::DEFAULT;
    }
}
