<?php

namespace App\Data\Objects\ConsoleAPI\Navigation;

use App\Data\Enums\NavigationTypeEnum;
use App\Models\Navigation;

class NavigationObject
{
    public int $id;
    public int $created_at;
    public string $url;
    public NavigationTypeEnum $type;
    public int $sort;

    /**
    * @var NavigationVariantObject[]
    */
    public array $variants;

    public function __construct(Navigation $navigation)
    {
        $this->id = $navigation->id;
        $this->created_at = $navigation->created_at->timestamp;
        $this->url = $navigation->url;
        $this->type = $navigation->type;
        $this->sort = $navigation->sort;

        $this->variants = $navigation->variants
            ->map(fn ($variant) => new NavigationVariantObject($variant))
            ->sortBy('language_id')
            ->toArray();

    }
}
