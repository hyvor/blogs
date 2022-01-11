<?php

namespace App\Http\Controllers\Delivery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;

use App\Twig\Filters\AssetsFilters;
use App\Twig\Filters\LanguageFilter;


use App\Twig\Tags\MyTagExtension;
// use App\Twig\Tags\Node\TwigNode;
// use App\Twig\Tags\TokenParser\TwigTokenParser;


use App\Twig\Functions\TwigFunctions;



class ThemeDeleveryController extends Controller
{
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
        // dd($geturl);
        $this->themeRepo->getUrl($geturl);

        // $twigFilter = new TwigFilters;
        // $twigFilter->postNotification($geturl);

        return $this->themeRepo->getUrl($geturl);
    }













    

    /* 
    *
    * This is the index page of the bolg
    *
    */
    public function index(){
        return $this->themeRepo->index();
    }

    /* 
    *
    * This is the author page of the bolg
    *
    */
    public function author(Request $request){

        $getAuthor = $request->slug;
        $this->themeRepo->author($getAuthor);
        return $this->themeRepo->author($getAuthor);
    }


    /* 
    *
    * This is the tags page of the bolg
    *
    */
    public function tag(Request $request){
        $getTag = $request->tagName;
        $this->themeRepo->tag($getTag);
        return $this->themeRepo->tag($getTag);
    }


    /* 
    *
    * This is the posts & pages of the bolg
    *
    */
    public function pages(Request $request){

        $getPage = $request->name;
        $this->themeRepo->pages($getPage);
        return $this->themeRepo->pages($getPage);
    }

    /* 
    *
    * This is the language change function. (.env)
    *
    */
    public function languageChange(){
        $dotenv = Dotenv::createImmutable(public_path('themes/'));
        $dotenv->load();

        $language = getenv('LANG');
        dd($language);
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
