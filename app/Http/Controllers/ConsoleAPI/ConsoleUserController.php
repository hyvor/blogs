<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use App\Domains\User\UserRepositoryInterface;
use Illuminate\Http\Request;

class ConsoleUserController extends Controller
{

    public function createBlog(Request $request)
    {

    }

    public function changeSort(Request $request)
    {
        
    }

    /*
    *
    * ConsoleAPI Settings->users
    *
    */
    public static function getAuthor(Request $request)
    {
        return 'get Author';
    }

    public static function createAuthor(Request $request) {

        return 'create Author';
    }

    public static function updateAuthor(Request $request)
    {
       return 'update author';
    }

    public static function deleteAuthor(Request $request)
    {
       return 'delete author';
    }

    /*
    *
    *
    * *** ConsoleAPI Posts->Author ***
    *
    * This function will get all the Authors and display it in an order (Post_Count)
    */
    public static function getAuthorList(Request $request){
        
        return 'get Author list';
    }

    /*
    *
    * This function will save the post_id and the Author_id in the post_Author table.
    * (This function should also save the number of posts in the count table.)
    *
    */
    public static function createPostAuthor(Request $request){
        
        return 'create post Author';
    }

    /*
    * 
    * This function will get the selected Authors and display it in the react-select box.
    *
    */
    public static function getPostAuthor(Request $request){

        return 'get post Author';
    }

    /*
    * 
    * This function will remove the selected Author.
    *
    */
    public static function removePostAuthor(Request $request){
       return 'hello world';
    }


}
