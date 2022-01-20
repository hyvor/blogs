<?php

namespace App\Domains\Theme;

use App\Domains\Theme\ThemeRepository;

use ScssPhp\ScssPhp\Compiler;
use Twig\Environment;

class TemplateRepository {

    /* 
    *
    *
    * This is the index page of the bolg
    *
    *
    */
    public static function index()
    {  
        $fileName = ThemeRepository::deliverThemeData();

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
    }

    /* 
    *
    *
    * This is the AUTHOR page of the bolg
    *
    *
    */
    public static function author(){
        $homePage = ThemeRepository::deliverThemeData();
        $authorPage = ThemeRepository::deliverAuthorData();
        // dd($tagPage);

        if($authorPage != null)
        {
            foreach($homePage as $getHomePage){

                if($getHomePage['name'] == 'index.twig'){
       
                    $homeContent = $getHomePage['content'];
    
                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'index.html' => $homeContent,
                    ));
                    $twig = new \Twig\Environment($loader);
           
                    echo $twig->render('index.html', 
                        array(
                            // 'style' => $stylecss , 
                            'name' => 'Finnaly done', 
                            'occupation' => 'must get the approvel', 
                            // 'script' => $script
                        ));
                }
            }
        }
        else
        {
            return 'author page content';
        }
    }

    /* 
    *
    *
    * This is the tags page of the bolg
    *
    *
    */
    public static function tag()
    {

        $homePage = ThemeRepository::deliverThemeData();
        $tagPage = ThemeRepository::deliverTagData();
        // dd($tagPage);

        if($tagPage != null)
        {
            foreach($homePage as $getHomePage){

                if($getHomePage['name'] == 'index.twig'){
       
                    $homeContent = $getHomePage['content'];
    
                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'index.html' => $homeContent,
                    ));
                    $twig = new \Twig\Environment($loader);
           
                    echo $twig->render('index.html', 
                        array(
                            // 'style' => $stylecss , 
                            'name' => 'Finnaly done', 
                            'occupation' => 'must get the approvel', 
                            // 'script' => $script
                        ));
                }
            }
        }
        else
        {
            return 'tag content';
        }
        // dd('hi I am the tag');
        // return "This is the page for tags";
    }


    /* 
    *
    *
    * This is the posts & pages of the bolg
    *
    *
    */
    public static function pages()
    {
        // $type = 1; //check page, post or redirect
        $type = ThemeRepository::isPage();
        // dd($type);

        if($type == 1){
            dd('this is a page');

        }else if($type == 0){

            dd('this is a post');

            $fileName = $this->themeRepo->deliverThemeData();

            foreach($fileName as $singlePage){
                if($singlePage['name'] == 'single.twig'){

                    $singleContent = $singlePage['content'];
                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'single.html' => $singleContent,
                    ));
                    $twig = new \Twig\Environment($loader);
       
                    echo $twig->render('single.html', 
                    array(
                        'Title'=> "hyvor blog",
                        'name' => 'hyvor' , 
                        'number'=> "123456789",
                        'test' => 'loader', 
                    ));
                }
            }

        }else{
            dd('this is an redirect');
        }

    }

}