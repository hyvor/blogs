<?php

namespace App\Data\Objects\ConsoleAPI\Navigation;

use App\Models\Navigation;

class NavigationObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public ?string $url;
    public ?string $type;
    public ?string $sort;

    /**
    * @var array<int, NavigationVariantObject>
    */
    public array $variants;

    public function __construct(Navigation $navigation)
    {
        $this->id = $navigation->id;
        $this->created_at = $navigation->created_at->timestamp;
        $this->updated_at = $navigation->updated_at->timestamp;
        $this->blog_id = $navigation->blog_id;
        $this->url = $navigation->url;
        $this->type = $navigation->type;
        $this->sort = $navigation->sort;

        $this->variants = $navigation->variants
        ->map(fn ($variant) => new NavigationVariantObject($variant, $navigation))
        ->keyBy('language_id')
        ->toArray();
    }
}
