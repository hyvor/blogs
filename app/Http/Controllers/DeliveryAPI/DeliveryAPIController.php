<?php
namespace App\Http\Controllers\DeliveryAPI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Domains\Theme\AssetsRepository;
use App\Domains\Theme\TemplateRepository;

use App\Domains\Theme\ThemeRepository;

use App\Models\Blog;
use App\Models\BlogThemeFile;

use App\Domains\Theme\Twig\AssetsFilters;
use App\Domains\Theme\Twig\LanguageFilter;
use App\Domains\Theme\Twig\TwigFunctions;

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

        $route = new RouteCollection();
        
        // $route->add('assets', new Route('/api/delivery/v0/blog/test?path=/assets/{name}'));
        $route->add('assets', new Route('/assets/{name}'));
        $route->add('tag', new Route('/tag/{slug}'));
        $route->add('author', new Route('/author/{slug}'));
        $route->add('pages', new Route('/{slug}'));
        $route->add('home', new Route('/'));

        /* 
        * Connecting with the main route
        */
        $context = new RequestContext();
        $matcher = new UrlMatcher($route, $context);
        $attributes = $matcher->match($path);  

        $routeValue = $attributes['_route'];
        // dd($routeValue);
        
        if($routeValue == 'assets' ) {
            // Returns the assets of the theme
            $urlName = $attributes['name'];
            return AssetsRepository::assets($urlName);  

        } else if($routeValue == 'pages') {
            // dd('This is for posts, pages and redirects');
            // Returns the sub pages of th theme
            return TemplateRepository::pages();

        } else if($routeValue == 'tag') {
            dd('jj');
            // This is the tag page
            return TemplateRepository::tag();

        } else if($routeValue == 'author') {
            // This is the author page
            return TemplateRepository::author();
        } else if($routeValue == 'home')
        {
            // Returns the home page of th theme
            return TemplateRepository::index();
        }
        else
        {
            dd('Error this page is not working');
        }

    }

    // Bloger Theme Select
    public function selectTheme(){

        // dd('hello world');
        // $theme_id = 1;
        // ThemeRepository::copyTheme($theme_id);

        $selectedTheme = ThemeRepository::copyTheme();
        return 'hello world';
    }
}