<?php
namespace App\Http\Controllers\DeliveryAPI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;
use App\Domains\Theme\Delivery\AssetsLogic;
use App\Domains\Theme\Delivery\ThemeLogic;
use App\Domains\Theme\ThemeRepository;

use App\Models\Blog;
use App\Models\BlogThemeFile;

use App\Domains\Theme\Twig\Filters\AssetsFilters;
use App\Domains\Theme\Twig\Filters\LanguageFilter;
use App\Domains\Theme\Twig\Tags\MyTagExtension;
use App\Domains\Theme\Twig\Functions\TwigFunctions;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing;


/** 
 * 
 * 
 */

class DeliveryAPIController {

    static function get(Request $request) {

        // dd("ggg");

        $subdomain = $request->route('subdomain');
        $path = $request->input('path');

        // dd($path);

        // here goes the code to determine which type of file this is by its path...
        // Create the error pages
        // chnage the type the type to reeirect.
        // Type of redirect. 301 and 302



        // $getSubdomainId = Blog::where('subdomain' , $subdomain)
        //                     ->first('id');

        // $subdomainCheck = BlogThemeFile::where('blog_id' , $getSubdomainId) 
        //                     ->get(); 
        // dd($subdomainCheck);




        /**
         * Code you currently have in DeliveryAPIRepository goes here
         */

         // dd($geturl);
        $request = Request::createFromGlobals();
        $route = new RouteCollection();
        
        $route->add('assets', new Route('/api/delivery/v0/blog/test?path=/assets/{name}'));
        // $route->add('assets', new Route('/assets/{name}'));
        $route->add('pages', new Route('/api/delivery/v0/blog/test?path=/{name}'));
        $route->add('tag', new Route('/api/delivery/v0/blog/test?path=/tag/{name}'));
        $route->add('author', new Route('/api/delivery/v0/blog/test?path=/author/{name}'));
        $route->add('home', new Route('/'));

        // dd($route);
        // dd($path);

        /* 
        *
        * Connecting with the main route
        *
        */
        $Context = new RequestContext($path);
        $Context->fromRequest($request);
        $Matcher = new UrlMatcher($route, $Context);
        $Attribute = $Matcher->match($_SERVER['REQUEST_URI']);  
        // $Attribute = $Matcher->match($request->getbaseUrl());        

        if($Attribute['_route'] == 'assets' )
        {
            // Returns the assets of the theme
            $urlName = $Attribute['name'];
            return AssetsLogic::assets($urlName);            
        }
        else if($Attribute['_route'] == 'page')
        {
            dd('gg');
            // Returns the sub pages of th theme
            return ThemeLogic::pages();
        }
        else if($Attribute['_route'] == 'tag')
        {
            // This is the tag page
            return ThemeLogic::tag();
        }
        else if($Attribute['_route'] == 'author')
        {
            // This is the author page
            return ThemeLogic::author();
        }
        else if($Attribute['_route'] == 'home')
        {
            // Returns the home page of th theme
            return ThemeLogic::index();
        }
        else
        {
            dd('this is for the home page');
        }

    }

    // Bloger Theme Select
    public function selectTheme(){

        // dd('hello world');
        // $theme_id = 1;
        // ThemeRepository::copyTheme($theme_id);

        $selectedTheme = ThemeRepository::copyTheme();
        return view('themes.select_theme');
    }
}