<?php

namespace App\Domains\Integrations\Shopify;

use App\Data\Enums\NavigationTypeEnum;
use App\Domains\Blog\Fillers\FillerInterface;
use App\Domains\Navigation\NavigationRepository;
use App\Models\Blog;

/**
 * This replaces the main NavigationFiller for Shopify blogs
 */
class ShopifyNavigationFiller implements FillerInterface
{

    public function __construct(private Blog $blog)
    {
    }

    public function fill()
    {

        NavigationRepository::createNavigation(
            $this->blog,
            'Shop',
            '/',
            NavigationTypeEnum::HEADER
        );

    }
}