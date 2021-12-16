<?php

namespace App\Http\Controllers\Delivery;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ThemesRepositoryInterface;


class ThemeDeleveryController extends Controller
{
    private $themeRepo;

    public function __construct(ThemesRepositoryInterface $themeRepository)
    {
        $this->themeRepo = $themeRepository;
    }

    // Home page of the blog
    public function index(){

        $fileName = $this->themeRepo->deliverThemeData();
        $style = $this->style();
        $script = $this->script();

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
                        'style' => $style , 
                        'name' => 'Finnaly done', 
                        'occupation' => 'must get the approvel', 
                        'script' => $test
                    ));
            }
        }
        // return "This is the home page"; 
    }

    // Author page of the blog
    public function author($slug){
        return "This is the author page";
    }


    // Tag page of the blog
    public function tag(Tag $tag){
        return "This is the page for tags";
    }


    // Posts and Pages of the blog
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

    // return the styles
    public function style(){

        $fileName = $this->themeRepo->deliverThemeData();
        foreach($fileName as $homePage){
            if($homePage['name'] == 'style.css'){
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
}
