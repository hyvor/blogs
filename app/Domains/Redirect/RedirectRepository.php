<?php
namespace App\Domains\Redirect;

use App\Data\Enums\RedirectTypeEnum;
use App\Models\Redirect;

Class RedirectRepository
{
    public static function getRedirects( int $blogId, int $limit = 0, int $offset = 0,)
    {
        return Redirect::where('blog_id','=', $blogId)
            ->offset($offset)
            ->limit($limit)
            ->latest()
            ->get();
    }

    public static function createRedirect(
        int $blogId, string $path, 
        string $to, RedirectTypeEnum $type = RedirectTypeEnum::TEMPORARY)
    {
        return Redirect::create([
            'blog_id' => $blogId,
            'path' => $path,
            'to' => $to,
            'type' => (string) $type->value,
        ]);
    }

    public static function updateRedirect(int $blogId ,int $id, string $oldUrl, string $newUrl, $type){

        $redirect = Redirect::find($id);
        $redirect->old_url=$oldUrl;
        $redirect->new_url=$newUrl;
        $redirect->type=$type;

        $redirect->save();
    }

    public static function deleteRedirect(int $id){
        $data = Redirect::find($id);
        $data->delete();
    }

    public static function findRedirectForPath(int $blogId, string $path) : Redirect|null
    {

        return Redirect::where('blog_id', $blogId)
            ->where('path', $path)
            ->first();

    }

}


