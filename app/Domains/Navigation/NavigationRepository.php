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
<<<<<<< HEAD
        return Navigation::where('blog_id','=', $blogId)
=======
        return Navigation::where('blog_id', '=', $blogId)
            // ->orderBy('sort', 'desc')
>>>>>>> rasif-import
            ->orderBy('sort')
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
    }

    public static function createNavigation(
<<<<<<< HEAD
        int $blogId, 
        string $name, 
        string $url, 
        NavigationTypeEnum $type, 
        int $sort 
    ) : Navigation
    {
        return Navigation::create([
            'blog_id' => $blogId,
            'name' => $name,
            'url' => $url,
            'type' => $type->value, 
            'sort' => $sort
=======
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
>>>>>>> rasif-import
        ]);
    }

    public static function updateNavigation(
<<<<<<< HEAD
        int $id, 
        string $name, 
        string $url, 
=======
        int $id,
        string $navigationName,
        string $navigationUrl,
>>>>>>> rasif-import
        NavigationTypeEnum $type
    ): void {
        $navigation = Navigation::find($id);
<<<<<<< HEAD
        $navigation->name=$name;
        $navigation->url=$url;
        $navigation->type=$type->value;
=======
        $navigation->name = $navigationName;
        $navigation->url = $navigationUrl;
        $navigation->type = $type->value;
>>>>>>> rasif-import

        $navigation->save();
    }

<<<<<<< HEAD
    public static function deleteNavigation(int $id) : void {
        $delete = Navigation::find($id);
        $delete->delete();
=======
    public static function deleteNavigation(int $id): void
    {
        $data = Navigation::find($id);
        $data->delete();
>>>>>>> rasif-import
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
