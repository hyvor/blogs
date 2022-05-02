<?php

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\NavigationTypeEnum;
use App\Domains\Navigation\NavigationRepository;
use App\Models\Blog;

class NavigationFiller implements FillerInterface
{

    private $navs = [
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

    public function __construct(private Blog $blog) {

        if ($blog->type === BlogTypeEnum::DEV) {

            $this->navs[] = [
                'type' => NavigationTypeEnum::HEADER,
                'name' => 'Content Style',
                'url' => '/content-style'
            ];

        }

    }

    public function fill()
    {

        foreach ($this->navs as $nav) {
            NavigationRepository::createNavigation(
                $this->blog,
                $nav['name'],
                $nav['url'],
                $nav['type'],
            );
        }

    }
}