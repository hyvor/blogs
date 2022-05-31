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
    ): Navigation {

        $navigation = Navigation::create([
            'blog_id' => $blog->id,
            'url' => $url,
            'type' => $type->value,
            'sort' => $sort,
        ]);

        $language = $blog
            ->languages()
            ->where('is_primary', true)
            ->first();

        self::createNavigationVariant(
            $navigation,
            $language,
            $name
        );

        return $navigation;
    }

    public static function updateNavigation(
        int $id,
        int $languageId,
        ?string $name,
        ?string $url,
        NavigationTypeEnum $type
    ): void {
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

    public static function createNavigationVariant(
        Navigation $navigation,
        Language $language,
        string $name
    )
    {

        NavigationVariant::create([
            'navigation_id' => $navigation->id,
            'language_id' => $language->id,
            'name' => $name
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
