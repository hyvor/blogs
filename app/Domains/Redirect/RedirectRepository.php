<?php
/**
 * Created by VS code.
 * User: Rasif
 * Date: 2022-01-22
 * Time: 11:20 AM
 */

namespace App\Domains\Redirect;

use Illuminate\Http\Request;

use App\Models\Redirect;

Class RedirectRepository
{
    /*
    *
    * This function will get all the redirect data from the database.
    *
    */
    public static function getRedirects( int $blogID)
    {
        return Redirect::where('blog_id','=', $blogID)
            ->offset(0)
            ->limit(100)
            ->latest()
            ->get();
        // return Redirect::paginate(7);
    }

    /*
    *
    * This function will create new redirect data and save it in the database.
    *
    */
    public static function createRedirect(int $blogID, string $oldURL, string $newURL, $type )
    {
        return Redirect::create([
            'blog_id' => $blogID,
            'old_url' => $oldURL,
            'new_url' => $newURL,
            'type' => $type, 
        ]);
    }

    /*
    *
    * This function will update an existing redirect and save it in the database.
    *
    */
    public static function updateRedirect(int $blogID ,int $id, string $oldURL, string $newURL, $type){

        // return Redirect::find($id)
        // ->create([
        //     'blog_id' => $blogID,
        //     'old_url' => $oldURL,
        //     'new_url' => $newURL,
        //     'type' => $type, 
        // ]);

        $redirect = Redirect::find($id);
        $redirect->old_url=$oldURL;
        $redirect->new_url=$newURL;
        $redirect->type=$type;

        $redirect->save();
    }

    /*
    *
    * This function will delete an redirect from the database.
    *
    */
    public static function deleteRedirect(int $id){
        $data = Redirect::find($id);
        $data->delete();
    }

}


