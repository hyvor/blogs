<?php

namespace App\Repositories\DeliveryAPI;
use App\Repositories\DeliveryAPI\DeliveryAPIRepositoryInterface;
use App\Repositories\Theme\AssetsLogic;
use App\Repositories\Theme\ThemeLogic;

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
    public function getUrl($geturl){

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
        $Matcher = new UrlMatcher($route, $Context);
        $Attribute = $Matcher->match($request->getPathInfo());        
        // dd($Attribute);


        if($Attribute['_route'] == 'assets' )
        {
            // Returns the assets of the theme
            $urlName = $Attribute['name'];
            return AssetsLogic::assets($urlName);            
        }
        else if($Attribute['_route'] == 'page')
        {
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


    /* ThemeQueryType
    *
    * Selecting the theme from ThemeFiles table & pasting it in the BlogThemeFiles table
    *
    */
    public function copyTheme($theme_id){

        $themeID = Theme::select('id')
        ->where('id','=', $theme_id)
        ->get();

        if($themeID){

            $blogId = Blog::select('id')
            ->value('id');

            // $blogId = BlogThemeFile::join('blogs', 'blogs.id', '=', 'blog_theme_files.blogs_id')
            // ->where('id','=', $theme_id)
            // ->first();

            $themeFileName= ThemeFile::select('name','content','type')
            ->where('theme_id','=', $theme_id)
            ->get();

            foreach($themeFileName as $key => $themeFile){
                BlogThemeFile::create([
                    'blog_id'=> $blogId,
                    'name'=>$themeFile->name,
                    'content'=>$themeFile->content,
                    'type'=>$themeFile->type,
                ]);
            }

            // foreach($themeFileName as $key => $themeFile){
            //     BlogThemeFile::create([
            //         'blog_id'=> $blogId,
            //         'name'=>$themeFile['name'],
            //         'content'=>$themeFile['content'],
            //         'type'=>$themeFile['type'],
            //     ]);
            // }

        }
        
        return $themeID;
    }




}