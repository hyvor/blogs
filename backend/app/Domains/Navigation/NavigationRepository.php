<?php

namespace App\Domains\Navigation;

use App\Data\Enums\NavigationTypeEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Support\Collection;

class NavigationRepository
{
    /**
     * @return Collection<int, Navigation>
     */
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
    ): Navigation {
        $navigation = Navigation::create([
            'blog_id' => $blog->id,
            'url' => $url,
            'type' => $type,
            'sort' => $sort,
        ]);

        $language = LanguageRepository::getPrimaryLanguage($blog);

        NavigationVariant::create([
            'navigation_id' => $navigation->id,
            'language_id' => $language->id,
            'name' => $name,
        ]);

        NavigationChangedEvent::dispatch($navigation);
        ;

        return $navigation;
    }

    public static function updateNavigation(
        Navigation $navigation,
        string $url,
        NavigationTypeEnum $type,
    ): Navigation {
        $navigation->url = $url;
        $navigation->type = $type;
        $navigation->save();

        NavigationChangedEvent::dispatch($navigation);

        return $navigation;
    }

    public static function deleteNavigation(Navigation $navigation) : void
    {
        $navigation->variants->map(fn ($variant) => self::deleteNavigationVariant($variant));
        $navigation->delete();

        NavigationChangedEvent::dispatch($navigation);
    }

    public static function getNavigationVariant(Navigation $navigation, Language $language): ?NavigationVariant
    {
        return NavigationVariant::where('navigation_id', $navigation->id)
            ->where('language_id', $language->id)
            ->first();
    }

    public static function createNavigationVariant(
        Navigation $navigation,
        Language $language,
        ?string $name
    ): NavigationVariant {
        $variant = NavigationVariant::create([
            'navigation_id' => $navigation->id,
            'language_id' => $language->id,
            'name' => $name,
        ]);

        NavigationVariantChangedEvent::dispatch($variant);

        return $variant;
    }

    public static function updateNavigationVariant(NavigationVariant $variant, string $name): NavigationVariant
    {
        $variant->name = $name;
        $variant->save();

        NavigationVariantChangedEvent::dispatch($variant);

        return $variant;
    }

    public static function deleteNavigationVariant(NavigationVariant $variant) : void
    {
        $variant->delete();
        NavigationVariantChangedEvent::dispatch($variant);
    }

    public static function getCount(Blog $blog, NavigationTypeEnum $type): int
    {
        return Navigation::where('blog_id', '=', $blog->id)
            ->where('type', $type)
            ->count();
    }

    /**
     * @param int[] $navIds
     */
    public static function updateSort(Blog $blog, array $navIds) : void
    {
        $i = 1;
        foreach ($navIds as $navId) {
            Navigation::where('blog_id', $blog->id)
                ->where('id', $navId)
                ->update([
                    'sort' => $i,
                ]);
            $i++;
        }
    }
}
