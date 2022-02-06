<?php
namespace App\Domains\Redirect;

use App\Models\Redirect;

Class RedirectRepository
{
    public static function getRedirects( int $blogId)
    {
        return Redirect::where('blog_id','=', $blogId)
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
    }

    public static function createRedirect(int $blogId, string $oldUrl, string $newUrl, $type )
    {
        return Redirect::create([
            'blog_id' => $blogId,
            'old_url' => $oldUrl,
            'new_url' => $newUrl,
            'type' => $type, 
        ]);
    }

    public static function updateRedirect(int $blogId ,int $id, string $oldUrl, string $newUrl, $type){

        // return Redirect::find($id)
        // ->create([
        //     'blog_id' => $blogId,
        //     'old_url' => $oldUrl,
        //     'new_url' => $newUrl,
        //     'type' => $type, 
        // ]);

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

}


