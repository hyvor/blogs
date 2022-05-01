<?php

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\NavigationTypeEnum;
use App\Domains\Navigation\NavigationRepository;
use App\Models\Blog;

class NavigationFiller implements FillerInterface
{

    const NAVS = [
        [
            'type' => NavigationTypeEnum::HEADER,
            'name' => 'Home',
            'url' => '/'
        ],
        [
            'type' => NavigationTypeEnum::HEADER,
            'name' => 'About',
            'url' => '/about'
        ],
        [
            'type' => NavigationTypeEnum::FOOTER,
            'name' => 'Privacy Policy',
            'url' => '/privacy'
        ],
        [
            'type' => NavigationTypeEnum::FOOTER,
            'name' => 'Contact',
            'url' => '/contact'
        ],
    ];

    public function __construct(private Blog $blog) {}

    public function fill()
    {

        foreach (self::NAVS as $nav) {
            NavigationRepository::createNavigation(
                $this->blog,
                $nav['name'],
                $nav['url'],
                $nav['type'],
            );
        }

    }
}