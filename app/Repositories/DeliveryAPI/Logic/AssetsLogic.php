<?php

namespace App\Repositories\DeliveryAPI\Logic;

// use App\Repositories\DeliveryAPI\AssetsLogicInterface;
use App\Repositories\DeliveryAPI\Eloquent\ThemeQuery;

use ScssPhp\ScssPhp\Compiler;


// class AssetsLogic implements AssetsLogicInterface{
class AssetsLogic {

    private $themeRepo;

    public function __construct(ThemeQuery $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }

    /* 
    *
    * Fetching the assets data from the database and passing it to an URL
    *
    */
    public function assets($geturl){

        // dd($geturl);
        $this->themeRepo->deliverAssets($geturl);

        if($geturl == 'style.css'){

            $path = file_get_contents(base_path('public/themes/styles/index.scss'), true);
            // Scss compiler
            $compiler = new Compiler();
            $style = $compiler->compileString($path)->getCss();
            return $style;

        }
        else{
            
            $file = $this->themeRepo->deliverAssets($geturl);
            dd($file);

            foreach($file as $singleAsset){
                $assetName = $singleAsset['name'];
            }
            if($assetName){
                // This function is used to get the file extention
                $extentionFilter = pathinfo($assetName, PATHINFO_EXTENSION);
                $extention = '.'.$extentionFilter;

                // $extention = '.png';
                if($extention = '.js'){
                    // dd("I am java script");
                    $script = file_get_contents(base_path('public/themes/assets/script.js'), true);
                    return $script;
                }
                else if($extention = '.svg'){

                }
                else if ($extention = '.jpeg'){

                }
                else if ($extention = '.png'){
                    $png = file_get_contents(base_path('public/themes/assets/1.png'), true);
                    return $png;
                }
                else{

                }
            }
        }
    }

    
}