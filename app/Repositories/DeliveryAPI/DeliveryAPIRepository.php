<?php

namespace App\Repositories\DeliveryAPI;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;
use App\Repositories\DeliveryAPI\Logic\AssetsLogic;
use App\Repositories\DeliveryAPI\Logic\ThemeLogic;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing;



Class DeliveryAPIRepository implements DeliveryAPIRepositoryInterface
{

    private $themeLog;
    private $assetsLog;


    public function __construct(ThemeLogic $themeLogic , AssetsLogic $assetsLogic )
    {
        $this->themeLog = $themeLogic;
        $this->assetsLog = $assetsLogic;
    }

    /* 
    *
    * This is the author page of the bolg
    *
    */
    public function getUrl($geturl , $request){

        // dd($geturl);
       
        $route = new RouteCollection();
        
        $route->add('assets', new Route('/assets/{name}'));
        $route->add('pages', new Route('{name}'));
        // dd($route);
        // $route->add('home', new Route(''));
        // $route->add('pages', new Route('tag/{name}'));
        // $route->add('pages', new Route('author/{name}'));


        /* 
        *
        * Call the assets
        *
        */
        $assetContext = new RequestContext($geturl);
        $assetContext->fromRequest($request);
        $assetMatcher = new UrlMatcher($route, $assetContext);
        $assetAttribute = $assetMatcher->match($request->getPathInfo());        
        
        dd($assetAttribute);
        // $generator = new Routing\Generator\UrlGenerator($routes, $context);
        // echo $generator->generate('script.js');

        /* 
        *
        * Call the pages
        *
        */
        $pageContext = new RequestContext($geturl);
        $pageContext->fromRequest($request);
        $pageMatcher = new UrlMatcher($route, $pageContext);
        $pageAttribute = $pageMatcher->match($request->getPathInfo()); 

        // dd($pageAttribute);


        if($assetAttribute['name'])
        {
           return $this->assetsLog->assets($geturl);
        }
        else if($pageAttribute['name'])
        {
            dd('this is for the page route');
        }
        else{
            dd('none');
        }
    }



}