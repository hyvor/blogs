<?php
namespace App\Http\Controllers\DeliveryAPI;

use Illuminate\Http\Request;

use App\Domains\Theme\AssetsRepository;
use App\Domains\Theme\TemplateRepository;
use App\Domains\Theme\ThemeRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;


class DeliveryAPIController {

    static function handle(Request $request, Blog $blog) {

        $request->validate([
            'path' => 'required|string'
        ]);

        /**
         * Delivery API says "how to serve a path"
         *
         * Takes two inputs:
         *  subdomain
         *  path
         *
         * Returns an output as specified [here]()
         */

        $path = $request->input('path');

        $route = new RouteCollection();

        $route->add('styles', new Route('/styles.css'));
        $route->add('asset', new Route('/assets/{fileName}'));
        $route->add('tag', new Route('/tag/{slug}'));
        $route->add('author', new Route('/author/{slug}'));
        $route->add('dynamic', new Route('/{slug}')); // redirect|post|page
        $route->add('home', new Route('/'));

        /*
        * Connecting with the main route
        */
        $context = new RequestContext();
        $matcher = new UrlMatcher($route, $context);
        $attributes = $matcher->match($path);

        $type = $attributes['_route'];

        if($type == 'asset' ) {
            $fileName = $attributes['fileName'];
            [ $content, $contentType ] = AssetsRepository::getAsset($blog->id, $fileName);
        } else if($type == 'pages') {
            // dd('This is for posts, pages and redirects');
            // Returns the sub pages of th theme
            return TemplateRepository::pages();

        } else if($type == 'tag') {
            // This is the tag page
            return TemplateRepository::tag();

        } else if($type == 'author') {
            // This is the author page
            return TemplateRepository::author();
        } else if($type == 'home')
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

        $selectedTheme = ThemeRepository::copyTheme();
        return 'hello world';
    }
}
