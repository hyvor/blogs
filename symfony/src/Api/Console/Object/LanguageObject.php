<?php

namespace App\Api\Console\Object;

use App\Entity\Language;

class LanguageObject
{
    public int $id;
    public string $code;
    public string $name;
    public bool $is_primary;
    public string $direction;

    public function __construct(Language $language)
    {
        $this->id = $language->getId();
        $this->code = $language->getCode();
        $this->name = $language->getName();
        $this->is_primary = $language->isPrimary();
        $this->direction = $language->getDirection();
    }
}
