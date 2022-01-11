<?php
namespace App\Http\Controllers\DeliveryAPI;

use Illuminate\Http\Client\Request;

/**
 * 
 * 
 */

class DeliveryAPIController {

    static function get(Request $request) {

        $subdomain = $request->route('subdomain');
        $path = $request->input('path');

        // $blog = BlogRepository::getBlogBySubdomain($subdomain)

        // $allFiles = ThemeRepository::getAllFiles()

        // here goes the code to determine which type of file this is by its path...

        /**
         * Code you currently have in DeliveryAPIRepository goes here
         */

        

        

    }

}