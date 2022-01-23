<?php

namespace App\Domains\Theme;

use App\Domains\Theme\ThemeRepository;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use ScssPhp\ScssPhp\Compiler;
use Response;

class AssetsRepository
{
    public static function getAsset(int $blogId, string $file): array
    {



        return [
            $content,
            $contentType
        ];
    }

    public static function getStylesCss(int $blogId)
    {
    }

    /*
    *
    * Fetching the assets data from the database and passing it to an URL
    *
    */
    public static function assets($urlName)
    {

        ThemeRepository::deliverAssets($urlName);

        if ($urlName == 'style.css') {
            $path = ThemeRepository::deliverCSS();
            // dd($path['name']);
            $testArray = array($path['name'] , $path['content']);
            // dd($testArray);
            // $cars = array('@import "header";');

            // Scss compiler
            $compiler = new Compiler();
            $style = $compiler->registerFiles([ '@import "header";']);

            // dd($style);

            $collection = array(
                'type' => 'text',
                'mime_type' => 'text/css',
                'content' => $style
            );
            // dd($collection);
            return OutPutDeliveryAPI::renderContent($collection);

            // return Response::json($collection);
            // return response($style)->header('Content-Type' , 'text/css');
        } else {
            $file = ThemeRepository::deliverAssets($urlName);
            // dd($file);
            foreach ($file as $singleAsset) {
                $assetName = $singleAsset['name'];
            }

            if ($assetName) {
                // This function is used to get the file extention
                $extentionFilter = pathinfo($assetName, PATHINFO_EXTENSION);
                $extention = '.' . $extentionFilter;
                // dd($extention);
                // $extention = '.png';

                if ($extention == ".js") {
                    // dd('hell');
                    $script = file_get_contents(base_path('public/themes/assets/script.js'), true);
                    $collection = array(
                        'type' => 'text',
                        'mime_type' => 'application/js',
                        'content' => $script
                    );
                    return OutPutDeliveryAPI::renderContent($collection);
                } elseif ($extention == '.svg') {
                    $svg = file_get_contents(base_path('public/themes/assets/girl.svg'), true);
                    $collection = array(
                        'type' => 'text',
                        'mime_type' => 'image/svg+xml',
                        'content' => $svg
                    );
                    return OutPutDeliveryAPI::renderContent($collection);
                } elseif ($extention == '.jpg') {
                    $jpeg = file_get_contents(base_path('public/themes/assets/test.jpg'), true);
                    $convert = base64_encode($jpeg);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'image/jpeg',
                        'content' => $convert
                    );
                    return OutPutDeliveryAPI::renderContent($collection);

                    // return Response::json($collection);
                    // $backwords = base64_decode($convert);
                    // return response($backwords)->header('Content-Type', 'image/jpeg');
                } elseif ($extention == '.png') {
                    $png = file_get_contents(base_path('public/themes/assets/2.png'), true);
                    $convert = base64_encode($png);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'image/png',
                        'content' => $convert
                    );
                    return OutPutDeliveryAPI::renderContent($collection);
                } elseif ($extention = '.icon') {
                    // return response($icon)->header('Content-Type', 'image/x-icon');
                } elseif ($extention = '.gif') {
                    $gif = file_get_contents(base_path('public/themes/assets/coding.gif'), true);
                    $convert = base64_encode($gif);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'image/gif',
                        'content' => $convert
                    );
                    return OutPutDeliveryAPI::renderContent($collection);
                } elseif ($extention = '.woff2') {
                    $woff2 = file_get_contents(base_path('public/themes/assets/regular.woff2'), true);
                    $convert = base64_encode($woff2);
                    $collection = array(
                        'type' => 'binary',
                        'mime_type' => 'font/woff2',
                        'content' => $convert
                    );
                    return OutPutDeliveryAPI::renderContent($collection);
                } else {
                    return 'sorry we dont support this format';
                }
            }
        }
    }
}
