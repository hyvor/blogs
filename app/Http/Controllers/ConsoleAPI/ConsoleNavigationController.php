<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\ConsoleAPI\Navigation\NavigationObject;

use App\Domains\Navigation\NavigationRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
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


    public static function createNavigationVariant(Request $request)
    {
        $id = $request->input('id');
        $languageId = $request->input('languageId');
        $name = $request->input('name');
        $createVariant = NavigationRepository::createNavigationVariant($id, $languageId, $name);

        return response()->json($createVariant);
    }

    public function updateSort(Request $request)
    {
        $id = $request->route('id');
        $navigationSort = $request->input('navigationSort');

        $updateSort = NavigationRepository::updateDestinationSort($id, $navigationSort);

        return response()->json($updateSort);
    }

    public function updateSourceSort(Request $request)
    {
        // dd('hi bro daddy');
        $id = $request->route('id');
        $navigationSort = $request->input('sort');
        $updateSort = NavigationRepository::updateSourceSort($id, $navigationSort);

        return response()->json($updateSort);
    }
}
