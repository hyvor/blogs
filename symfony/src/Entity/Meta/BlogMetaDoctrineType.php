<?php

namespace App\Entity\Meta;

use Hyvor\Internal\Util\Doctrine\CustomJsonType;

class BlogMetaDoctrineType extends CustomJsonType
{
    public function getTypeName(): string
    {
        return BlogMeta::class;
    }
}
