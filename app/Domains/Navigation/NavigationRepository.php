<?php

namespace App\Domains\Navigation;

use App\Data\Enums\NavigationTypeEnum;
use App\Models\Blog;
use App\Models\Navigation;
use Illuminate\Support\Collection;

class NavigationRepository
{
    public const DEFUALT_HEADER_NAVIGATION = [
        [
            'name' => 'Home',
            'url' => '/',
        ],
        [
            'name' => 'About',
            'url' => '/about',
        ],
    ];

    public const DEFUALT_FOOTER_NAVIGATION = [
        [
            'name' => 'Privacy Policy',
            'url' => '/privacy',
        ],
        [
            'name' => 'Contact',
            'url' => '/contact',
        ],
    ];

    public static function getNavigations(int $blogId): Collection
    {
        return Navigation::where('blog_id', '=', $blogId)
            // ->orderBy('sort', 'desc')
            ->orderBy('sort')
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
    }

    public static function createNavigation(
        Blog $blog,
        string $navigationName,
        string $navigationUrl,
        NavigationTypeEnum $type,
        int $sort = 0
    ): Navigation {
        return Navigation::create([
            'blog_id' => $blog->id,
            'name' => $navigationName,
            'url' => $navigationUrl,
            'type' => $type->value,
            'sort' => $sort,
        ]);
    }

    public static function updateNavigation(
        int $id,
        string $navigationName,
        string $navigationUrl,
        NavigationTypeEnum $type
    ): void {
        $navigation = Navigation::find($id);
        $navigation->name = $navigationName;
        $navigation->url = $navigationUrl;
        $navigation->type = $type->value;

        $navigation->save();
    }

    public static function deleteNavigation(int $id): void
    {
        $data = Navigation::find($id);
        $data->delete();
    }

    public static function getHeaderSort(): Navigation
    {
        $headerSort = Navigation::where('type', '=', 'header')
           ->get('sort')
           ->last();

        return $headerSort;
    }

    public static function getFooterSort(): Navigation
    {
        return Navigation::where('type', '=', 'footer')
           ->get('sort')
           ->last();
    }

    public static function updateDestinationSort(int $id, $navigationSort)
    {
        $navigation = Navigation::find($id);
        $navigation->sort = $navigationSort;

        $navigation->save();
    }

    public static function updateSourceSort(int $id, $navigationSort)
    {
        $navigation = Navigation::find($id);
        $navigation->sort = $navigationSort;

        $navigation->save();
    }

    public static function getHeaderCount(): int
    {
        return Navigation::where('type', '=', 'header')
           ->count();
    }

    public static function getFooterCount(): int
    {
        return Navigation::where('type', '=', 'footer')
           ->count();
    }
}
