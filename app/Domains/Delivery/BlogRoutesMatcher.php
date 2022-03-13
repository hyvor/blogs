<?php
namespace App\Domains\Delivery;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\RouteMatcher\RouteMatcher;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Language;

class BlogRoutesMatcher {

    private Blog $blog;
    private string $path;

    private Language $language;

    private ?DeliveryAPIResponseObject $responseObject = null;

    public function __construct(Blog $blog, string $path) {

        $this->blog = $blog;
        $this->path = $path;

        $this->setLanguage();
        $this->matchNonPostRoutes();

    }

    /**
     * Set the language based on the path prefix
     */
    private function setLanguage() {

        // Get fr from /fr/hello-world
        $pathExploded = explode('/', $this->path);
        $possibleLanguageCode = $pathExploded[1] ?? null;
    
        if ($possibleLanguageCode && strlen($possibleLanguageCode) <= 12) {
            // fetch all languages and find out the correct one
            $langs = $this->blog->languages;

            // messing with the collection
            $defaultLang = $langs->where('is_primary', true)->first();
            $nonDefaultLangs = $langs->where('is_primary', false);

            $lang = $nonDefaultLangs->firstWhere('code', $possibleLanguageCode);

            if ($lang) {
                /**
                 * Set new path to match, removing the language part
                 */
                $this->path = '/' . implode( "/", array_slice($pathExploded, 2) );
            } else {
                $lang = $defaultLang;
            }

        } else {
            // fetch only the default one
            $lang = LanguageRepository::getPrimaryLanguage($this->blog);
        }

        $this->language = $lang;

    }

    private function matchNonPostRoutes() {
        $nonPostRoutes = $this->blog->routes->filter(function ($route) {
            return $route !== 'post' && $route !== 'page';
        });

        $routeMatcher = new RouteMatcher($this->path);

        foreach ($nonPostRoutes as $route) {

            $match = $route->match;
            $defaults = [];
            $requirements = [];

            /**
             * Add suffix
             * Which can be "page/x" for pagination
             * or /feed
             */
            if ($route->posts_filter !== null) {
                $match .= '/{suffix}';
                $defaults = [
                    'suffix' => null
                ];
                // feed or page number
                $requirements = [
                    'suffix' => '(feed|(page\/\d+))'
                ];
            }

            $routeMatcher->add($route->name, $match, $defaults, $requirements);            
        }

        $matchedRoute = $routeMatcher->match();



    }

    public function getResponseObject() {
        return $this->responseObject;
    }

}