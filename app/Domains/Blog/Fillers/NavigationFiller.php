<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\NavigationTypeEnum;
use App\Domains\Navigation\NavigationRepository;
use App\Models\Blog;

class NavigationFiller implements FillerInterface
{
    /**
     * @var array<array{type: NavigationTypeEnum, name: string, url: string}>
     */
    private array $navs = [
        [
            'type' => NavigationTypeEnum::HEADER,
            'name' => 'About',
            'url' => '/about',
        ],
        [
            'type' => NavigationTypeEnum::HEADER,
            'name' => 'Contact',
            'url' => '/contact',
        ],
        [
            'type' => NavigationTypeEnum::FOOTER,
            'name' => 'Privacy Policy',
            'url' => '/privacy',
        ],
    ];

    public function __construct(private Blog $blog)
    {
    }

    public function fill() : void
    {
        if (
            $this->blog->type === BlogTypeEnum::DEV ||
            $this->blog->type === BlogTypeEnum::PREVIEW
        ) {

            // send about and contact to footer
            $this->navs[0]['type'] = NavigationTypeEnum::FOOTER;
            $this->navs[1]['type'] = NavigationTypeEnum::FOOTER;

            $this->navs[] = [
                'type' => NavigationTypeEnum::HEADER,
                'name' => 'Content Style',
                'url' => '/content-style',
            ];

            $user = $this->blog->users[0];

            if ($user) {
                $this->navs[] = [
                    'type' => NavigationTypeEnum::HEADER,
                    'name' => 'Author',
                    'url' => '/author/' . $user->slug,
                ];
            }

            $tag = $this->blog->tags[0];
            if ($tag) {
                $this->navs[] = [
                    'type' => NavigationTypeEnum::HEADER,
                    'name' => 'Tag',
                    'url' => '/tag/' . $tag->slug,
                ];
            }
        }

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
