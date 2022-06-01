<?php

namespace App\Domains\Navigation;

use App\Data\Enums\NavigationTypeEnum;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Support\Collection;

class NavigationRepository
{

    public static function getNavigations(Blog $blog): Collection
    {
        return $blog->navigations()->get();
    }

    public static function createNavigation(
        Blog $blog,
        string $name,
        string $url,
        NavigationTypeEnum $type,
        int $sort = 0
    ): Navigation
    {

        $navigation = Navigation::create([
            'blog_id' => $blog->id,
            'url' => $url,
            'type' => $type->value,
            'sort' => $sort,
        ]);

        $language = LanguageRepository::getPrimaryLanguage($blog);

        self::createNavigationVariant(
            $navigation,
            $language,
            $name
        );

        return $navigation;
    }

    public static function updateNavigation(
        Navigation $navigation,
        string $url
    ) : Navigation
    {

        $navigation->url = $url;
        $navigation->save();

        return $navigation;

    }

    public static function deleteNavigation(Navigation $navigation)
    {
        $navigation->delete();
    }

    public static function createNavigationVariant(
        Navigation $navigation,
        Language $language,
        string $name
    ) {
        NavigationVariant::create([
            'navigation_id' => $navigation->id,
            'language_id' => $language->id,
            'name' => $name,
        ]);
    }

    /*
    *
    * This functions are used for navigation sort.
    */
    public static function getHeaderSort(): Navigation
    {
        return Navigation::where('type', '=', 'header')
           ->get('sort')
           ->last();
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

    public static function getCount(Blog $blog, NavigationTypeEnum $type): int
    {
        return Navigation::where('blog_id', '=', $blog->id)
            ->where('type', $type)
            ->count();
    }

    public static function getFooterCount(Blog $blog): int
    {
        return Navigation::where('blog_id', '=', $blog->id)
            ->where('type', '=', 'footer')
           ->count();
    }
}
