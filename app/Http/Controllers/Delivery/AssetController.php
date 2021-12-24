<?php

namespace App\Http\Controllers\Delivery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\DeliveryAPI\AssetsLogicInterface;


class AssetController extends Controller
{
    private $assetsRepo;

    public function __construct(AssetsLogicInterface $assetsRepository)
    {
        $this->assetsRepo = $assetsRepository;
    }

    /* 
    *
    * Fetching the assets data from the database and passing it to an URL
    *
    */
    public function assets(Request $request){

        $fileName = $request->fileName;
        $this->assetsRepo->assets($fileName);

        return $this->assetsRepo->assets($fileName);
    }

    
    public function testScripts(){
        $script = file_get_contents(base_path('public/themes/assets/script.js'), true);
        return $script;
    }

    public function testStyles(){
        // 
    }
}
