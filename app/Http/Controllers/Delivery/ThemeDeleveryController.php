<?php

namespace App\Http\Controllers\Delivery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ThemesRepositoryInterface;

use ScssPhp\ScssPhp\Compiler;
use Twig\Environment;
use Dotenv\Dotenv;

class ThemeDeleveryController extends Controller
{
    private $themeRepo;

    public function __construct(ThemesRepositoryInterface $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }

    /* 
    *
    * This is the index page of the bolg
    *
    */
    public function index(){

        $fileName = $this->themeRepo->deliverThemeData();
        $style = $this->style();
        $script = $this->script();

        $compiler = new Compiler();
        $stylecss = $compiler->compileString($style)->getCss();

        // $test = mb_convert_encoding($script, "UTF-8", "HTML-ENTITIES");
        // $test =  htmlspecialchars_decode($script, ENT_QUOTES);
        // dd($test);

        foreach($fileName as $homePage){

            if($homePage['name'] == 'index.twig'){
   
                $homeContent = $homePage['content'];

                $loader = new \Twig\Loader\ArrayLoader(array(
                    'index.html' => $homeContent,
                ));
                $twig = new \Twig\Environment($loader);
       
                echo $twig->render('index.html', 
                    array(
                        'style' => $stylecss , 
                        'name' => 'Finnaly done', 
                        'occupation' => 'must get the approvel', 
                        'script' => $script
                    ));
            }
        }
        // return "This is the home page"; 
    }

    /* 
    *
    * This is the author page of the bolg
    *
    */
    public function author($slug){
        return "This is the author page";
    }


    /* 
    *
    * This is the tags page of the bolg
    *
    */
    public function tag(Tag $tag){
        return "This is the page for tags";
    }


    /* 
    *
    * This is the posts & pages of the bolg
    *
    */
    public function pages(){

        $fileName = $this->themeRepo->deliverThemeData();
        $style = $this->style();
        $script = $this->script();

        foreach($fileName as $singlePage){
            if($singlePage['name'] == 'single.twig'){

                $singleContent = $singlePage['content'];
                $loader = new \Twig\Loader\ArrayLoader(array(
                    'single.html' => $singleContent,
                ));
                $twig = new \Twig\Environment($loader);
       
                echo $twig->render('single.html', 
                array(
                    'style' => $style , 
                    'Title'=> "hyvor blog",
                    'name' => 'hyvor' , 
                    'number'=> "123456789",
                    'test' => 'loader', 
                    'script'=> $script,
                ));
            }
        }
        // return "This is all the other pages";
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


    // return the styles
    public function style(){

        $fileName = $this->themeRepo->deliverThemeData();
        foreach($fileName as $homePage){
            if($homePage['name'] == 'index.scss'){
                $homeStyle = $homePage['content'];
                return $homeStyle;
            }
        }

    }

    // return the javascripts
    public function script(){

        $fileName = $this->themeRepo->deliverThemeData();
        foreach($fileName as $homePage){
            if($homePage['name'] == 'script.js'){
                $homeScript = $homePage['content'];
                return $homeScript;
            }
        }

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

        echo $twig->render('index.html', 
            array(
                // 'style' => $style , 
                'name' => 'Finnaly done', 
                'occupation' => 'must get the approvel', 
                // 'script' => $script
            ));

    }

}
