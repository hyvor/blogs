<?php

namespace App\Domains\Navigation;

use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\DataAPI\NavObject;
use App\Models\Blog;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Support\Collection;
use App\Models\Language;
use App\Domains\Language\LanguageRepository;

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

    public static function getNavigations(Blog $blog): Collection
    {
        $language = LanguageRepository::getPrimaryLanguage($blog);

        $navigation = Navigation::where('blog_id', '=', $blog->id)
            ->join('navigation_variants', function ($join) use ($language) {
                $join->on('navigation_variants.navigation_id', '=', 'navigations.id');
                $join->where('navigation_variants.language_id', '=', $language->id);
            })
            ->select('navigations.*')
            ->latest()
            ->get();

        return $navigation;
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

        $navigationId = $navigation->id;
        $getLanguage = $blog->languages()->where('is_primary', true)->first();
        $primaryLanguage = $getLanguage->id;

        NavigationVariant::create([
            'navigation_id' => $navigationId,
            'language_id' => $primaryLanguage,
            'name' => $name,
        ]);

        return $navigation;
    }

    public static function updateNavigation(
        int $id,
        int $languageId,
        ?string $name,
        ?string $url,
        NavigationTypeEnum $type
    ): void 
    {
        // $navigation = Navigation::find($id);
        // $navigation->name = $navigationName;
        // $navigation->url = $navigationUrl;
        // $navigation->type = $type->value;
        // $navigation->save();

        Navigation::find($id)
                ->update([
                    'url' => $url,
                    'type' => $type,
                ]);

        NavigationVariant::where('navigation_id', '=', $id)
            ->where('language_id', '=', $languageId)
            ->update([
                'name' => $name,
            ]);
    }

    public static function deleteNavigation(int $id, int $languageId): void
    {
        // $data = Navigation::find($id);
        // $data->delete();

        $language = Language::where('id', '=', $languageId)
        ->value('is_primary');

        if ($language == 0) {
            NavigationVariant::where('navigation_id', '=', $id)
                ->where('language_id', '=', $languageId)
                ->delete();
        } else {
            NavigationVariant::where('navigation_id', '=', $id)
                ->where('language_id', '=', $languageId)
                ->delete();

            Navigation::find($id)
                ->delete();
        }
    }

    /*
    *
    * This functions are used for navigation variant.
    */
    public static function createNavigationVariant($navigationId, $languageId, $name)
    {
        $language = Language::where('id', '=', $languageId)
            ->value('is_primary');

        if ($language == 0) {
            $navigationVariantCheck = NavigationVariant::where('navigation_id', '=', $navigationId)
                ->where('language_id', '=', $languageId)
                ->first();

            if ($navigationVariantCheck == null) {
                NavigationVariant::create([
                    'navigation_id' => $navigationId,
                    'language_id' => $languageId,
                    'name' => $name,
                ]);
            }
        }
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

    public static function getHeaderCount(Blog $blog): int
    {
        return Navigation::where('blog_id', '=', $blog->id)
            ->where('type', '=', 'header')
           ->count();
    }

    public static function getFooterCount(Blog $blog): int
    {
        return Navigation::where('blog_id', '=', $blog->id)
            ->where('type', '=', 'footer')
           ->count();
    }
}