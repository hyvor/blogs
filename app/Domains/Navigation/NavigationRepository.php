<?php
namespace App\Domains\Navigation;

use App\Models\Navigation;
use App\Data\Enums\NavigationTypeEnum;
use Illuminate\Support\Collection;

Class NavigationRepository
{
    public static function getNavigations(int $blogId) : Collection
    {
        return Navigation::where('blog_id','=', $blogId)
            ->orderBy('sort')
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
    }

    public static function createNavigation(
        int $blogId, 
        string $name, 
        string $url, 
        NavigationTypeEnum $type, 
        int $sort 
    ) : Navigation
    {
        return Navigation::create([
            'blog_id' => $blogId,
            'name' => $name,
            'url' => $url,
            'type' => $type->value, 
            'sort' => $sort
        ]);
    }

    public static function updateNavigation(
        int $id, 
        string $name, 
        string $url, 
        NavigationTypeEnum $type
    ) : void {

        $navigation = Navigation::find($id);
        $navigation->name=$name;
        $navigation->url=$url;
        $navigation->type=$type->value;

        $navigation->save();
    }

    public static function deleteNavigation(int $id) : void {
        $delete = Navigation::find($id);
        $delete->delete();
    }

    public static function getHeaderSort() : Navigation {
        $headerSort =  Navigation::where('type','=', 'header')
           ->get('sort')
           ->last();

        return $headerSort;
    }

    public static function getFooterSort() : Navigation {
        return Navigation::where('type','=', 'footer')
           ->get('sort')
           ->last();
    }

    public static function updateDestinationSort(int $id, $navigationSort){
        $navigation = Navigation::find($id);
        $navigation->sort=$navigationSort;

        $navigation->save();
    }

    public static function updateSourceSort(int $id, $navigationSort){
        $navigation = Navigation::find($id);
        $navigation->sort=$navigationSort;

        $navigation->save();
    }

    public static function getHeaderCount() : int {
        return Navigation::where('type','=', 'header')
           ->count();
    } 

    public static function getFooterCount() : int {
        return Navigation::where('type','=', 'footer')
           ->count();
    }
}


