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
        $navigation->variants->map(fn ($variant) => self::deleteNavigationVariant($variant));
        $navigation->delete();
    }


    public static function getNavigationVariant(Navigation $navigation, Language $language) : ?NavigationVariant
    {
        return NavigationVariant::where('navigation_id', $navigation->id)
            ->where('language_id', $language->id)
            ->first();
    }

    public static function createNavigationVariant(
        Navigation $navigation,
        Language $language,
        ?string $name
    ) : NavigationVariant
    {
        return NavigationVariant::create([
            'navigation_id' => $navigation->id,
            'language_id' => $language->id,
            'name' => $name,
        ]);
    }

    public static function updateNavigationVariant(NavigationVariant $variant, string $name) : NavigationVariant
    {
        $variant->name = $name;
        $variant->save();
        return $variant;
    }

    public static function deleteNavigationVariant(NavigationVariant $variant)
    {
        $variant->delete();
    }

    public static function getCount(Blog $blog, NavigationTypeEnum $type): int
    {
        return Navigation::where('blog_id', '=', $blog->id)
            ->where('type', $type)
            ->count();
    }

}
