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

use \Mockery ;



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
    * mockery/mockery
    */
    public function getUrl($geturl, $request){

        // dd($geturl);
        // $request = Mockery::mock('App\Http\Requests\UpdateMerchant');

        $route = new RouteCollection();
        
        $route->add('assets', new Route('/assets/{name}'));
        $route->add('pages', new Route('{name}'));
        // $route->add('home', new Route('/'));
        $route->add('tag', new Route('tag/{name}'));
        $route->add('author', new Route('author/{name}'));

        /* 
        *
        * Connecting with the main route
        *
        */
        $Context = new RequestContext($geturl);
        $Context->fromRequest($request);
        $Matcher = new UrlMatcher($route, $Context);
        $Attribute = $Matcher->match($request->getPathInfo());        
        // dd($Attribute);



        if($Attribute['_route'] == 'assets' )
        {
           return $this->assetsLog->assets($geturl);
        }
        else if($Attribute['_route'] == 'page')
        {
            dd('this is for the page route');
        }
        else if($Attribute['_route'] == 'tag')
        {
            dd('this links the tags page');
        }
        else if($Attribute['_route'] == 'author')
        {
            dd('this links for the author page');
        }
        else{
            dd('this is for the home page');
        }
    }



}