<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\ConsoleAPI\Navigation\NavigationObject;

use App\Data\Objects\ConsoleAPI\Navigation\NavigationVariantObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Navigation\NavigationRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Navigation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleNavigationController extends Controller
{

    public function get(Blog $blog)
    {

        $navs = NavigationRepository::getNavigations($blog)->mapInto(NavigationObject::class);
        return response()->json($navs);

    }

    public function create(Request $request, Blog $blog)
    {

        $request->validate([
            'url' => 'required|string',
            'name' => 'required|string',
            'type' => ['required', new Enum(NavigationTypeEnum::class)]
        ]);

        $url = $request->input('url');
        $name = $request->input('name');
        $type = NavigationTypeEnum::from($request->input('type'));

        $count = NavigationRepository::getCount($blog, $type);

        if ($count >= config('limits.max_navigations_per_type_per_blog')) {
            throw new TrustedException('Limit exceeded');
        }

        $navigation = NavigationRepository::createNavigation($blog, $name, $url, $type);

        return response()->json(new NavigationObject($navigation));

    }

    public function update(Request $request, Navigation $navigation)
    {
        $request->validate([
            'url' => 'required|string'
        ]);

        $url = $request->input('url');

        $navigation = NavigationRepository::updateNavigation($navigation, $url);

        return response()->json(new NavigationObject($navigation));
    }

    public function delete(Navigation $navigation)
    {
        NavigationRepository::deleteNavigation($navigation);
        return response()->json();
    }


    public static function createVariant(Request $request, Blog $blog, Navigation $navigation)
    {

        $request->validate([
            'language_id' => 'required|integer',
            'name' => 'string|nullable'
        ]);

        $languageId = $request->input('language_id');
        $name = $request->input('name');

        $language = LanguageRepository::getLanguageById($blog, $languageId);

        if (!$language) {
            throw new TrustedException('Language not found');
        }

        $variant = NavigationRepository::createNavigationVariant($navigation, $language, $name);

        return response()->json(new NavigationVariantObject($variant));
    }

    public static function updateVariant(Request $request, Navigation $navigation, Language $language)
    {

        $request->validate([
            'name' => 'required|string'
        ]);

        $variant = NavigationRepository::getNavigationVariant($navigation, $language);
        $name = $request->input('name');

        if (!$variant) {
            throw new TrustedException('Variant not found');
        }

        NavigationRepository::updateNavigationVariant($variant, $name);

        return response()->json(new NavigationVariantObject($variant));

    }

    public static function deleteVariant(Navigation $navigation, Language $language)
    {

        $variant = NavigationRepository::getNavigationVariant($navigation, $language);
        if (!$variant) {
            throw new TrustedException('Variant not found');
        }

        NavigationRepository::deleteNavigationVariant($variant);

        return response()->json();
    }

    public function updateSort(Request $request, Blog $blog)
    {

        $request->validate([
            'ids' => 'array',
            'ids.*' => 'integer'
        ]);

        $ids = $request->input('ids');

        NavigationRepository::updateSort($blog, $ids);

        return response()->json();
    }


}
