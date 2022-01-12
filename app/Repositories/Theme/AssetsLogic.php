<?php

namespace App\Repositories\Theme;
use App\Domains\Theme\ThemeRepository;

use ScssPhp\ScssPhp\Compiler;
use Response;

class AssetsLogic {

    /* 
    *
    * Fetching the assets data from the database and passing it to an URL
    *
    */
    public static function assets($urlName){

        // dd($urlName);
        ThemeRepository::deliverAssets($urlName);

        if($urlName == 'style.css'){

            // dd('style');
            $path = file_get_contents(base_path('public/themes/styles/index.scss'), true);

            // Scss compiler
            $compiler = new Compiler();
            $style = $compiler->compileString($path)->getCss();

            $collection = array(
                'type' => 'text',
                'mime_type' => 'text/css',
                'content' => $style
            );
            // dd($collection);
            // return $collection;
            return Response::json($style);
            // return response($style)->header('Content-Type' , 'text/css');
        }
        else{
            
            // $file = ThemeRepository::deliverAssets($urlName);
            // // dd($file);

            // foreach($file as $singleAsset){
            //     $assetName = $singleAsset['name'];
            // }

            $assetName = '1.js';
            // dd($assetName);
            if($assetName){
                // This function is used to get the file extention
                $extentionFilter = pathinfo($assetName, PATHINFO_EXTENSION);
                $extention = '.'.$extentionFilter;
                // dd($extention);
                // $extention = '.png';
                
                if($extention == ".js"){
                    $script = file_get_contents(base_path('public/themes/assets/script.js'), true);
                    return response($script)->header('Content-Type', 'application/js');
                    // return $script;
                }
                else if($extention == '.svg'){
                    // return response($svg)->header('Content-Type', 'image/svg+xml');
                }
                else if ($extention == '.jpg'){
                    $jpeg = file_get_contents(base_path('public/themes/assets/test.jpg'), true);
                    return response($jpeg)->header('Content-Type', 'image/jpeg');
                    // return $jpeg;
                }
                else if ($extention == '.png'){
                    $png = file_get_contents(base_path('public/themes/assets/2.png'), true);
                    return response($png)->header('Content-Type', 'image/png');
                    // return $png;
                }
                else if ($extention = '.icon'){
                    // return response($icon)->header('Content-Type', 'image/x-icon');
                }
                else if ($extention = '.gif'){
                    // return response($gif)->header('Content-Type', 'image/gif');
                }
                else if ($extention = '.font'){
                    // return response($font)->header('Content-Type', 'font/woff2');
                }
                else{
                    return 'sorry we dont support this format';
                }
            }
        }
    }

    
}