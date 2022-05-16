<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\NavigationTypeEnum;
use App\Data\Objects\ConsoleAPI\NavigationObject;

use App\Domains\Navigation\NavigationRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class ConsoleNavigationController extends Controller
{
    public function getNavigations(Blog $blog)
    {
        $getData = NavigationRepository::getNavigations($blog->id)
            ->map(function ($navigation) {
                return new NavigationObject($navigation);
            });

        return response()->json($getData);
    }

    public function createNavigation(Request $request, Blog $blog)
    {
        $getHeaderCount = NavigationRepository::getHeaderCount();
        $getFooterCount = NavigationRepository::getFooterCount();

        $headerCount = $getHeaderCount < 8;
        $footerCount = $getFooterCount < 8;
<<<<<<< HEAD
        
        $request->validate([
            'name' => 'required|string',
            'url' => 'required|string',
            'type' => 'required|string',
        ]); 
        $name = $request->input('name');
        $url = $request->input('url');
        $type = NavigationTypeEnum::from($request->input('type'));

        if($type->value == 'header'){
            $sortHead =  NavigationRepository::getHeaderSort();
            if($sortHead == null){
                $sort = 1;
            }else{
                $sort = $sortHead['sort'] + 1;
            }
        }
        
        if($type->value == 'footer'){
            $sortFooter =  NavigationRepository::getFooterSort();
            if($sortFooter == null){
                $sort = 1;
            }else{
                $sort = $sortFooter['sort'] + 1;
            }
        }

        if($headerCount){
            $navigation = NavigationRepository::createNavigation($blog->id, $name, $url, $type, $sort);
            return response()->json(new NavigationObject($navigation));
        }else if($footerCount){
            $navigation = NavigationRepository::createNavigation($blog->id, $name, $url, $type, $sort);
            return response()->json(new NavigationObject($navigation));
        }else{
=======

        // $request->validate([
        //     'navigation_name' => 'required|string',
        //     'navigation_url' => 'required|string',
        //     'type' => 'required|string',
        // ]);
        $navigationName = $request->input('navigation_name');
        $navigationUrl = $request->input('navigation_url');
        $type = NavigationTypeEnum::from($request->input('type'));

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
            $createNavigation = NavigationRepository::createNavigation($blog->id, $navigationName, $navigationUrl, $type, $sort);

            return response()->json(new NavigationObject($createNavigation));
        } elseif ($footerCount) {
            $createNavigation = NavigationRepository::createNavigation($blog->id, $navigationName, $navigationUrl, $type, $sort);

            return response()->json(new NavigationObject($createNavigation));
        } else {
>>>>>>> rasif-import
            // return new TrustedException('You cant have more than 8 links', TrustedException::ERROR_INVALID_INPUT);
            abort(404);
        }
    }

<<<<<<< HEAD
    public function updateNavigation(Request $request, Blog $blog) {
        $request->validate([
            'name' => 'required|string',
            'url' => 'required|string',
            'type' => 'required|string',
        ]);
=======
    public function updateNavigation(Request $request, Blog $blog)
    {
        // $request->validate([
        //     'navigation_name' => 'required|string',
        //     'navigation_url' => 'required|string',
        //     'type' => 'required|string',
        // ]);
>>>>>>> rasif-import

        $id = $request->route('id');
        $name = $request->input('name');
        $url = $request->input('url');
        $type = NavigationTypeEnum::from($request->input('type'));

<<<<<<< HEAD
        $navigation = NavigationRepository::updateNavigation($id, $name, $url, $type);
        return response()->json($navigation);
=======
        $updateNavigation = NavigationRepository::updateNavigation($id, $navigationName, $navigationUrl, $type);

        return response()->json($updateNavigation);
>>>>>>> rasif-import
    }

    public function deleteNavigation(Request $request)
    {
        $id = $request->route('id');
<<<<<<< HEAD
        $navigation = NavigationRepository::deleteNavigation($id);
        return response()->json($navigation);
=======
        $deleteNavigation = NavigationRepository::deleteNavigation($id);

        return response()->json($deleteNavigation);
>>>>>>> rasif-import
    }

    public function updateSort(Request $request)
    {
        $id = $request->route('id');
        $navigationSort = $request->input('navigationSort');

        $updateSort = NavigationRepository::updateDestinationSort($id, $navigationSort);

        return response()->json($updateSort);
    }

<<<<<<< HEAD
    public function updateSourceSort(Request $request){
=======
    public function updateSourceSort(Request $request)
    {
        // dd('hi bro daddy');
>>>>>>> rasif-import
        $id = $request->route('id');
        $navigationSort = $request->input('sort');
        $updateSort = NavigationRepository::updateSourceSort($id, $navigationSort);

        return response()->json($updateSort);
    }
}
