<?php declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\Navigation;

use App\Exceptions\SafetyException;
use App\Models\NavigationVariant;

class NavigationVariantObject
{
    public int $navigation_id;

    public int $language_id;

    public ?string $name;

    public function __construct(NavigationVariant $variant)
    {
        $language = $variant->language;

        if (!$language) {
            throw new SafetyException('NavigationVariantObject: language not found');
        }

        $this->navigation_id = $variant->navigation_id;
        $this->language_id = $language->id;
        $this->name = $variant->name;
    }
}
