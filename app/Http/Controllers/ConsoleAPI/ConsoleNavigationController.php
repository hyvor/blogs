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
        
        $request->validate([
            'name' => 'required|string',
            'url' => 'required|string',
            'type' => 'required|string',
        ]); 
        $name = $request->input('name');
        $url = $request->input('url');
        $type = NavigationTypeEnum::from($request->input('type'));

        if($type->value == 'header'){
            $getSort =  NavigationRepository::getHeaderSort();
            if($getSort == null){
                $sort = 1;
            }else{
                $sort = $getSort['sort'] + 1;
            }
        }
        
        if($type->value == 'footer'){
            $getSort =  NavigationRepository::getFooterSort();
            if($getSort == null){
                $sort = 1;
            }else{
                $sort = $getSort['sort'] + 1;
            }
        }

        if($headerCount){
            $navigation = NavigationRepository::createNavigation($blog->id, $name, $url, $type, $sort);
            return response()->json(new NavigationObject($navigation));
        }else if($footerCount){
            $navigation = NavigationRepository::createNavigation($blog->id, $name, $url, $type, $sort);
            return response()->json(new NavigationObject($navigation));
        }else{
            // return new TrustedException('You cant have more than 8 links', TrustedException::ERROR_BAD_REQUEST);
            abort(404);
        }
    }

    public function updateNavigation(Request $request, Blog $blog) {
        $request->validate([
            'name' => 'required|string',
            'url' => 'required|string',
            'type' => 'required|string',
        ]);

        $id = $request->route('id');
        $name = $request->input('name');
        $url = $request->input('url');
        $type = NavigationTypeEnum::from($request->input('type'));

        $navigation = NavigationRepository::updateNavigation($id, $name, $url, $type);
        return response()->json($navigation);
    }

    public function deleteNavigation(Request $request) {
        $id = $request->route('id');
        $navigation = NavigationRepository::deleteNavigation($id);
        return response()->json($navigation);
    }

    public function updateSort(Request $request){
        $id = $request->route('id');
        $navigationSort = $request->input('navigationSort');

        $updateSort = NavigationRepository::updateDestinationSort($id, $navigationSort);
        return response()->json($updateSort);
    }

    public function updateSourceSort(Request $request){
        $id = $request->route('id');
        $navigationSort = $request->input('sort');
        $updateSort = NavigationRepository::updateSourceSort($id, $navigationSort);
        return response()->json($updateSort);
    }

}