<?php

namespace App\Http\Controllers\Delivery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ThemesRepositoryInterface;

use ScssPhp\ScssPhp\Compiler;


class AssetController extends Controller
{
    private $themeRepo;

    public function __construct(ThemesRepositoryInterface $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }

    /* 
    *
    * Fetching the assets data from the database and passing it to an URL
    *
    */
    public function assets(Request $request){

        $assetFile = $request->assetFile;
        $this->themeRepo->deliverAssets($assetFile);

        // $fileExtention = ;

        if($assetFile == 'style.css'){

            $path = file_get_contents(base_path('public/themes/styles/index.scss'), true);
            // Scss compiler
            $compiler = new Compiler();
            $style = $compiler->compileString($path)->getCss();
            return $style;

        }
        else{
            
            $file = $this->themeRepo->deliverAssets($assetFile);
            foreach($file as $singleAsset){
                $assetName = $singleAsset['name'];
            }
            if($assetName){
                // This function is used to get the file extention
                $extentionFilter = pathinfo($assetName, PATHINFO_EXTENSION);
                $extention = '.'.$extentionFilter;

                // $extention = '.png';
                if($extention = '.js'){
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



    
    public function testScripts(){
        $script = file_get_contents(base_path('public/themes/assets/script.js'), true);
        return $script;
    }

    public function testStyles(){
        // return "hello";
        // return "<style>" .$style. "</style>";
    }
}
