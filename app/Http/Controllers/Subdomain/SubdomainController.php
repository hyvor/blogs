<?php

namespace App\Http\Controllers\Subdomain;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Http\Controllers\Controller;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use Symfony\Component\Routing\RouteCollection;
// use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing;

class SubdomainController
{
    /*
    *
    * Resource :- https://symfony.com/doc/current/create_framework/routing.html
    *
    */
    public function getSubdomainData(Request $request)
    {

        $getScript = Request::create('http://blogs.hyvor.test/api/delivery/v0/blog/test?path=/assets/script.js', 'GET');

        $instance = json_decode(app()->handle($getScript)->getContent());

        dd($instance);
        response($content)->header('content-type', $instance['mime-type']);
        // $check = response()->json($instance);
        // dd($check);

        $path = $request->path();

        $route = new RouteCollection();

        $route->add('assets', new Route('/assets/{name}'));
        $route->add('pages', new Route('{name}'));
        $route->add('tag', new Route('tag/{name}'));
        $route->add('author', new Route('author/{name}'));
        $route->add('home', new Route('/'));


        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($route, $context);

        $Attribute = $matcher->match($request->getPathInfo());

        // dd($Attribute);

        $getContent = OutPutDeliveryAPI::renderContent($Attribute['name']);

        dd($getContent);

        if ($Attribute['_route'] == 'assets') {
            return 'css,js and other assets';
        } elseif ($Attribute['_route'] == 'page') {
            return 'dynamic pages';
        } elseif ($Attribute['_route'] == 'tag') {
            return 'tag pages.';
        } elseif ($Attribute['_route'] == 'author') {
            return 'author pages.';
        } elseif ($Attribute['_route'] == 'home') {
            return 'home page.';
        } else {
            dd('this is for the home page');
        }
    }


    /*
    *
    *
    * This to test how dose the theme files work.
    *
    */
    public function test()
    {

        // $path = file_get_contents(base_path('public/themes/styles/index.scss'), true);
        $index = file_get_contents(base_path('public/themes/templates/index.twig'), true);

        // Scss compiler
        // $compiler = new Compiler();
        // $style = $compiler->compileString($path)->getCss();

        $loader = new \Twig\Loader\ArrayLoader(array(
            'index.html' => $index,
        ));
        $twig = new \Twig\Environment($loader);
        $twig->addExtension(new AssetsFilters());
        $twig->addExtension(new LanguageFilter());
        $twig->addExtension(new MyTagExtension());
        $twig->addExtension(new TwigFunctions());


        echo $twig->render(
            'index.html',
            array(
                // 'style' => $style ,
                'name' => 'Finnaly done',
                'occupation' => 'must get the approvel',
                // 'script' => $script
            )
        );
    }
}
