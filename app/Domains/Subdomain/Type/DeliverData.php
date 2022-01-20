<?php

namespace App\Domains\Subdomain\Type;

use App\Domains\ThemeAssetsRepository;
use App\Domains\ThemeTemplateRepository;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing;

use Symfony\Component\HttpFoundation\Request;

use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\BlogThemeFile;
use App\Models\Blog;


Class DeliveryAPIRepository implements DeliveryAPIRepositoryInterface
{
    /* 
    *
    * This is the author page of the bolg
    * Resource :- https://symfony.com/doc/current/create_framework/routing.html
    *
    */
    public function getUrl($geturl)
    {

        // dd($geturl);
        $request = Request::createFromGlobals();
        $route = new RouteCollection();
        
        $route->add('assets', new Route('/assets/{name}'));
        $route->add('pages', new Route('{name}'));
        $route->add('tag', new Route('tag/{name}'));
        $route->add('author', new Route('author/{name}'));
        $route->add('home', new Route('/'));

        /* 
        *
        * Connecting with the main route
        *
        */
        $Context = new RequestContext($geturl);
        $Context->fromRequest($request);
        dd($Context);
        $Matcher = new UrlMatcher($route, $Context);
        $Attribute = $Matcher->match($request->getQueryString());        
        // dd($Attribute);


        if($Attribute['_route'] == 'assets' )
        {
            // Returns the assets of the theme
            $urlName = $Attribute['name'];
            return AssetsRepository::assets($urlName);            
        }
        else if($Attribute['_route'] == 'page')
        {
            // Returns the sub pages of th theme
            return TemplateRepository::pages();
        }
        else if($Attribute['_route'] == 'tag')
        {
            // This is the tag page
            return TemplateRepository::tag();
        }
        else if($Attribute['_route'] == 'author')
        {
            // This is the author page
            return TemplateRepository::author();
        }
        else if($Attribute['_route'] == 'home')
        {
            // Returns the home page of th theme
            return TemplateRepository::index();
        }
        else
        {
            dd('this is for the home page');
        }
    }

}