<?php

namespace App\Domains\Theme;

use App\Domains\Theme\ThemeRepository;
use ScssPhp\ScssPhp\Compiler;
use Response;

class AssetsRepository {

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
            $path = ThemeRepository::deliverCSS();
            // dd($path['content']);
            $testArray = json_encode($path['content'], true);
            // dd($testArray);
            $cars = array('@import "header";');
            
            // Scss compiler
            $compiler = new Compiler();
            $style = $compiler->registerFiles($cars)->getCss();

            dd($style); 




            $collection = array(
                'type' => 'text',
                'mime_type' => 'text/css',
                'content' => $style
            );
            // dd($collection);
            return Response::json($collection);
            // return response($style)->header('Content-Type' , 'text/css');
        }
        else{
            
            $file = ThemeRepository::deliverAssets($urlName);
            // dd($file);
            foreach($file as $singleAsset){
                $assetName = $singleAsset['name'];
            }
            // $assetName = '1.js';
            // dd($assetName);
            if($assetName){
                // This function is used to get the file extention
                $extentionFilter = pathinfo($assetName, PATHINFO_EXTENSION);
                $extention = '.'.$extentionFilter;
                // dd($extention);
                // $extention = '.png';
                
                if($extention == ".js"){
                    // dd('hell');
                    $script = file_get_contents(base_path('public/themes/assets/script.js'), true);
                    $collection = array(
                        'type' => 'text',
                        'mime_type' => 'application/js',
                        'content' => $script
                    );
                    return Response::json($collection);
                    // return response($script)->header('Content-Type', 'application/js');
                }
                else if($extention == '.svg'){
                    $svg = file_get_contents(base_path('public/themes/assets/girl.svg'), true);
                    $collection = array(
                        'type' => 'text',
                        'mime_type' => 'image/svg+xml',
                        'content' => $svg
                    );
                    return Response::json($collection);
                    // return response($svg)->header('Content-Type', 'image/svg+xml');
                }
                else if ($extention == '.jpg'){
                    $jpeg = file_get_contents(base_path('public/themes/assets/test.jpg'), true);
                    $convert = base64_encode($jpeg);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'image/jpeg',
                        'content' => $convert
                    );
                    return Response::json($collection);
                    // $backwords = base64_decode($convert);
                    // return response($backwords)->header('Content-Type', 'image/jpeg');                
                }
                else if ($extention == '.png'){
                    $png = file_get_contents(base_path('public/themes/assets/2.png'), true);
                    $convert = base64_encode($png);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'image/png',
                        'content' => $convert
                    );
                    return Response::json($collection);
                    // return response($png)->header('Content-Type', 'image/png');
                }
                else if ($extention = '.icon'){
                    // return response($icon)->header('Content-Type', 'image/x-icon');
                }
                else if ($extention = '.gif'){
                    $gif = file_get_contents(base_path('public/themes/assets/coding.gif'), true);
                    $convert = base64_encode($gif);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'image/gif',
                        'content' => $convert
                    );
                    return Response::json($collection);
                    // return response($gif)->header('Content-Type', 'image/gif');
                }
                else if ($extention = '.woff2'){
                    $woff2 = file_get_contents(base_path('public/themes/assets/regular.woff2'), true);
                    $convert = base64_encode($woff2);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'font/woff2',
                        'content' => $convert
                    );
                    return Response::json($collection);
                    // return response($font)->header('Content-Type', 'font/woff2');
                }
                else{
                    return 'sorry we dont support this format';
                }
            }
        }
    }

    
}