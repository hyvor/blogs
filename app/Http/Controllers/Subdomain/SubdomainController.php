<?php
namespace App\Http\Controllers\DeliveryAPI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;
use App\Domains\Theme\AssetsRepository;

use App\Domains\Theme\Twig\AssetsFilters;
use App\Domains\Theme\Twig\LanguageFilter;

use App\Domains\Theme\Twig\TwigFunctions;


/**
 * 
 * 
 */

class SubdomainController {



    private $themeRepo;

    public function __construct(DeliveryAPIRepositoryInterface $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }


    /* 
    *
    * This is the index page of the bolg
    *
    */
    public function getUrl(Request $request){
        
        $geturl = $request->path();
        $this->themeRepo->getUrl($geturl);
        return $this->themeRepo->getUrl($geturl);
    }

    /* 
    *
    * This is the index page of the bolg
    *
    */
    public function assets(){
       $asset = AssetsRepository::assets($urlName);
       return $asset;
    }



    /* 
    *
    * This is the index page of the bolg
    *
    */
    public function index(){
        // 
    }

    /* 
    *
    * This is the author page of the bolg
    *
    */
    public function author(Request $request){
    //
    }


    /* 
    *
    * This is the tags page of the bolg
    *
    */
    public function tag(Request $request){
        //
    }


    /* 
    *
    * This is the posts & pages of the bolg
    *
    */
    public function pages(Request $request){
        //
    }


    /*
    *
    *
    * Testing the theme files
    *
    */
    public function test(){

        // $path = file_get_contents(base_path('public/themes/styles/index.scss'), true);
        $index = file_get_contents(base_path('public/themes/templates/index.twig'), true);

        // Scss compiler
        // $compiler = new Compiler();
        // $style = $compiler->compileString($path)->getCss();

        $loader = new \Twig\Loader\ArrayLoader(array(
            'index.html' => $index,
        ));
        $twig = new \Twig\Environment($loader);
        $twig->addExtension(new AssetsFilters());
        $twig->addExtension(new LanguageFilter());
        $twig->addExtension(new MyTagExtension());
        $twig->addExtension(new TwigFunctions());


        echo $twig->render('index.html', 
            array(
                // 'style' => $style , 
                'name' => 'Finnaly done', 
                'occupation' => 'must get the approvel', 
                // 'script' => $script
            ));
            
    }

}