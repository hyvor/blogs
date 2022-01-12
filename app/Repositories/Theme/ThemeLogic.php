<?php

namespace App\Repositories\Theme;

use App\Domains\Themes\ThemeRepository;

use ScssPhp\ScssPhp\Compiler;
use Twig\Environment;
use Dotenv\Dotenv;


class ThemeLogic {

    /* 
    *
    * This is the index page of the bolg
    *
    */
    public static function index(){ 

        // $fileName = $this->themeRepo->deliverThemeData();
        $fileName = ThemeRepository::deliverThemeData();
       
        foreach($fileName as $homePage){
            if($homePage['name'] == 'index.scss'){
                $style = $homePage['content'];
            }
        }

        foreach($fileName as $homePage){
            if($homePage['name'] == 'script.js'){
                $script = $homePage['content'];
            }
        }

        // These function methods are not needed need to delete this after a bit of thinking.
        // $style = $this->style();
        // $script = $this->script();

        // Need to add this in the future
        // $compiler = new Compiler();
        // $stylecss = $compiler->compileString($style)->getCss();

        // I cant remember what is this
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
                        // 'style' => $stylecss , 
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
    public static function author(){
        dd('hello its done for the author');
        return "This is the author page";
    }


    /* 
    *
    * This is the tags page of the bolg
    *
    */
    public static function tag(){
        // dd('hi I am the tag');
        return "This is the page for tags";
    }


    /* 
    *
    * This is the posts & pages of the bolg
    *
    */
    public static function pages(){

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

}