<?php
namespace App\Domains\Navigation;

use App\Models\Navigation;

Class NavigationRepository
{
    public static function getNavigations( int $blogId){
        return Navigation::where('blog_id','=', $blogId)
            // ->orderBy('itemNumber', 'desc')
            ->orderBy('itemNumber')
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
    }

    public static function createNavigation(int $blogId, string $navigationName, string $navigationUrl, $type, int $itemNumber ){
        // dd($itemNumber);
        return Navigation::create([
            'blog_id' => $blogId,
            'name' => $navigationName,
            'url' => $navigationUrl,
            'type' => $type, 
            'itemNumber' => $itemNumber
        ]);
    }

    public static function updateNavigation(int $blogId ,int $id, string $navigationName, string $navigationUrl, $type){
        $navigation = Navigation::find($id);
        $navigation->name=$navigationName;
        $navigation->url=$navigationUrl;
        $navigation->type=$type;

        $navigation->save();
    }

    public static function deleteNavigation(int $id){
        $data = Navigation::find($id);
        $data->delete();
    }

    public static function getHeaderItemNumber(){
        return Navigation::where('type','=', 'header')
           ->get('itemNumber')
           ->last();
    }

    public static function getFooterItemNumber(){
        return Navigation::where('type','=', 'footer')
           ->get('itemNumber')
           ->last();
    }

    public static function updateDestinationItemNumber($id, $navigationItemNumber){
        $navigation = Navigation::find($id);
        $navigation->itemNumber=$navigationItemNumber;

        $navigation->save();
    }

    public static function updateSourceItemNumber($id, $navigationItemNumber){
        // dd($navigationItemNumber);

        $navigation = Navigation::find($id);
        $navigation->itemNumber=$navigationItemNumber;

        $navigation->save();
    }

    public static function getHeaderCount(){
        return Navigation::where('type','=', 'header')
           ->count();
    } 

    public static function getFooterCount(){
        return Navigation::where('type','=', 'footer')
           ->count();
    }

}


