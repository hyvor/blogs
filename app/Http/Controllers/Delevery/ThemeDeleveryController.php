<?php

namespace App\Http\Controllers\Delevery;

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

        $fileName = $this->themeRepo->index();

        $loader = new \Twig\Loader\ArrayLoader(array(
            'index.html' => $fileName,
        ));
        $twig = new \Twig\Environment($loader);
   
        echo $twig->render('index.html', array('name' => 'test five' , "occupation" => "testage"));

        // return "This is the home page"; 
    }

    // Author page of the blog
    public function author($name){
        return "This is the author page";
    }

    // Tag page of the blog
    public function tag(Tag $tag){
        return "This is the page for tags";
    }

    // Posts and Pages of the blog
    public function pages(){

        $singleTwigFileName = $this->themeRepo->postsAndPages();

        $loader = new \Twig\Loader\ArrayLoader(array(
            'single.html' => $singleTwigFileName,
        ));
        $twig = new \Twig\Environment($loader);
                
        echo $twig->render('single.html', array('name' => 'hello' , "occupation" => "test 3"));

        // return "This is all the other pages";
    }
}
