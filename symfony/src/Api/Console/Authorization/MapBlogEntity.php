<?php

namespace App\Api\Console\Authorization;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapBlogEntity
{
    /**
     * @param string $field entity field (and matching route parameter name) to look the entity
     * up by - e.g. 'uuid' for a route defined as `/thing/{uuid}`
     */
    public function __construct(
        public string $field = 'id',
    ) {}
}
