<?php
namespace App\Domains\Navigation;

use App\Models\Navigation;

Class NavigationRepository
{
    public static function getNavigations( int $blogId){
        return Navigation::where('blog_id','=', $blogId)
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
    }

    public static function createNavigation(int $blogId, string $navigationName, string $navigationUrl, $type ){
        return Navigation::create([
            'blog_id' => $blogId,
            'name' => $navigationName,
            'url' => $navigationUrl,
            'type' => $type, 
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

}


