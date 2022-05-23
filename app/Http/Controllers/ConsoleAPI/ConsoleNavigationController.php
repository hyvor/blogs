<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\ConsoleAPI\Navigation\NavigationObject;

use App\Domains\Navigation\NavigationRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class ConsoleNavigationController extends Controller
{
    public function getNavigations(Blog $blog)
    {
        $getData = NavigationRepository::getNavigations($blog)
            ->map(function ($navigation) {
                return new NavigationObject($navigation);
            });

        return response()->json($getData);
    }

    public function createNavigation(Request $request, Blog $blog)
    {
        // dd($request->input('type'));
        $getHeaderCount = NavigationRepository::getHeaderCount($blog);
        $getFooterCount = NavigationRepository::getFooterCount($blog);

        $headerCount = $getHeaderCount < 8;
        $footerCount = $getFooterCount < 8;

        // $request->validate([
        //     'name' => 'required|string',
        //     'url' => 'required|string',
        //     'type' => 'required|string',
        // ]);
        
        $name = $request->input('name');
        $url = $request->input('url');
        $type = NavigationTypeEnum::from($request->input('type'));

        if ($name == null) {
            throw new TrustedException('Name should not be empty.');
        }

        if ($url == null) {
            throw new TrustedException('url should not be empty.');
        }

        if ($type->value == 'header') {
            $getSort = NavigationRepository::getHeaderSort();
            if ($getSort == null) {
                $sort = 1;
            } else {
                $sort = $getSort['sort'] + 1;
            }
        }

        if ($type->value == 'footer') {
            $getSort = NavigationRepository::getFooterSort();
            if ($getSort == null) {
                $sort = 1;
            } else {
                $sort = $getSort['sort'] + 1;
            }
        }

        if ($headerCount) {
            $createNavigation = NavigationRepository::createNavigation($blog, $name, $url, $type, $sort);
            return response()->json(new NavigationObject($createNavigation));
        } elseif ($footerCount) {
            $createNavigation = NavigationRepository::createNavigation($blog, $name, $url, $type, $sort);
            return response()->json(new NavigationObject($createNavigation));
        } else {
            // return new TrustedException('You cant have more than 8 links', TrustedException::ERROR_UNPROCESSABLE);
            abort(404);
        }
    }

    public function updateNavigation(Request $request, Blog $blog)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'url' => 'required|string',
        //     'type' => 'required|string',
        // ]);

        $id = $request->route('id');
        $languageId = $request->input('languageId');
        $name = $request->input('name');
        $url = $request->input('url');
        $type = NavigationTypeEnum::from($request->input('type'));

        $updateNavigation = NavigationRepository::updateNavigation($id, $languageId, $name, $url, $type);

        return response()->json($updateNavigation);
    }

    public function deleteNavigation(Request $request)
    {
        $id = $request->route('id');
        $languageId = $request->input('languageId');
        $deleteNavigation = NavigationRepository::deleteNavigation($id, $languageId);

        return response()->json($deleteNavigation);
    }

    /**
    * Navigation variant section.
    */
    public static function createNavigationVariant(Request $request)
    {
        $id = $request->input('id');
        $languageId = $request->input('languageId');
        $name = $request->input('name');
        $createVariant = NavigationRepository::createNavigationVariant($id, $languageId, $name);

        return response()->json($createVariant);
    }

    /**
    * Sort navigation section.
    */
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