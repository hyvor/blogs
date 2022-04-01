<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exceptions\TrustedException;
use App\Domains\Navigation\NavigationRepository;
use App\Data\Objects\ConsoleAPI\NavigationObject;
use App\Models\Blog;
use App\Data\Enums\NavigationTypeEnum;


class ConsoleNavigationController extends Controller {

    public function getNavigations(Blog $blog) {

        // $getItemNumber =  NavigationRepository::getHeaderItemNumber();
        // dd($getItemNumber);

        $getData = NavigationRepository::getNavigations($blog->id)
            ->map(function ($navigation) {
            return new NavigationObject($navigation);
        });
        return response()->json($getData);
    }

    public function createNavigation(Request $request , Blog $blog) {

        $getHeaderCount = NavigationRepository::getHeaderCount();
        $getFooterCount = NavigationRepository::getFooterCount();


        $headerCount = $getHeaderCount < 8;
        $footerCount = $getFooterCount < 8;

        // validate the max length of 50
        
        // $request->validate([
        //     'navigation_name' => 'required|string',
        //     'navigation_url' => 'required|string',
        //     'type' => 'required|string',
        // ]);
        $navigationName = $request->input('navigation_name');
        $navigationUrl = $request->input('navigation_url');
        $type = NavigationTypeEnum::from($request->input('type'));

        if($type == 'header'){
            $getItemNumber =  NavigationRepository::getHeaderItemNumber();
            if($getItemNumber == null){
                $sort = 1;
            }else{
                $sort = $getItemNumber['sort'] + 1;
            }
        }

        if($type == 'footer'){
            $getItemNumber =  NavigationRepository::getFooterItemNumber();
            if($getItemNumber == null){
                $sort = 1;
            }else{
                $sort = $getItemNumber['sort'] + 1;
            }
        }

        if($headerCount){
            $createNavigation = NavigationRepository::createNavigation($blog->id, $navigationName, $navigationUrl, $type, $sort);
            return response()->json(new NavigationObject($createNavigation));
        }else if($footerCount){
            $createNavigation = NavigationRepository::createNavigation($blog->id, $navigationName, $navigationUrl, $type, $sort);
            return response()->json(new NavigationObject($createNavigation));
        }else{
            // return new TrustedException('You cant have more than 8 links', TrustedException::ERROR_BAD_REQUEST);
            abort(404);
        }
    }

    public function updateNavigation(Request $request, Blog $blog) {
        // $request->validate([
        //     'navigation_name' => 'required|string',
        //     'navigation_url' => 'required|string',
        //     'type' => 'required|string',
        // ]);

        $id = $request->route('id');
        $navigationName = $request->input('navigation_name');
        $navigationUrl = $request->input('navigation_url');
        $type =  NavigationTypeEnum::from($request->input('type'));

        $updateNavigation = NavigationRepository::updateNavigation($id, $navigationName, $navigationUrl, $type);
        return response()->json($updateNavigation);
    }

    public function deleteNavigation(Request $request) {
        $id = $request->route('id');
        $deleteNavigation = NavigationRepository::deleteNavigation($id);
        return response()->json($deleteNavigation);
    }

    public function updateSort(Request $request){
        // dd('hi bro daddy');
        $id = $request->route('navigationId');
        $navigationSort = $request->input('navigationSort');

        $updateSort = NavigationRepository::updateDestinationSort($id, $navigationSort);
        return response()->json($updateSort);
    }

    public function updateSourceSort(Request $request){
        // dd('hi bro daddy');
        $id = $request->route('sourceId');
        $navigationSort = $request->input('sort');
        $updateSort = NavigationRepository::updateSourceSort($id, $navigationSort);
        return response()->json($updateSort);
    }

}