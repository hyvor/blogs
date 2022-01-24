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
    public static function getRedirects(){
        return Redirect::get();
        // return 'get the redirect data ';
    }

    public static function createRedirects(Request $req ){
        $redirect = new Redirect;
        $redirect->blog_id=$req->blog_id;
        $redirect->old_url=$req->old_url;
        $redirect->new_url=$req->new_url;
        $redirect->type=$req->type;

        $redirect->save();
        // return Redirect::create(
        //     ['blog_id' =>'1'],
        //     ['old_url' =>'this is an old website'],
        //     ['new_url' =>'this is an new webdite'],
        //     ['type' =>'301'],
        // );
    }

    public static function deleteRedirects($id){
        $data = Redirect::find($id);
        $data->delete();
    }

    public static function updateRedirects(Request $req){
        $redirect = Redirect::find($req->id);
        $redirect->blog_id=$req->blog_id;
        $redirect->old_url=$req->old_url;
        $redirect->new_url=$req->new_url;
        $redirect->type=$req->type;

        $redirect->save();
    }
}


