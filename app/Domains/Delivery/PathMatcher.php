<?php
namespace App\Domains\Delivery;

use App\Data\Enums\RedirectTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\RouteMatcher\RouteMatcher;
use App\Domains\Delivery\RouteProcessors\AssetsProcessor;
use App\Domains\Delivery\RouteProcessors\MediaProcessor;
use App\Domains\Delivery\RouteProcessors\PreviewProcessor;
use App\Domains\Delivery\RouteProcessors\StylesProcessor;
use App\Domains\Redirect\RedirectRepository;
use App\Models\Blog;

class PathMatcher {

    public Blog $blog;
    public string $path;

    private bool $matched = false;
    private DeliveryAPIResponseObject $responseObject;

    public function __construct(Blog $blog, string $path) {

        // add leading slash if not
        if (!preg_match('/^\//', $path)) {
            $path = '/' . $path;
        }

        $this->blog = $blog;
        $this->path = $path;

        $this->callFuncs([
            'matchRedirect',
            'matchDefaultRoutes',
            'matchBlogRoutes'
        ]);

    }

    // calls functions with match check after each
    private function callFuncs(array $funcs) {
        foreach ($funcs as $func) {
            $this->{$func}();
            if ($this->matched())
                return;
        }
    }

    /**
     * Matches for redirects
     */
    private function matchRedirect() {

        $redirect = RedirectRepository::findRedirectForPath($this->blog, $this->path);

        if ($redirect) {
            $this->setMatched(
                DeliveryAPIResponseObject::forRedirect(
                    $redirect->to, 
                    RedirectTypeEnum::from($redirect->type)
                )
            );
        }

    }

    /**
     * This routes do not conflict
     * Therefore, it is not needed to match in a loop
     * If there's no match, nothing happens
     * The PathMatcher moves to the next step
     */
    private function matchDefaultRoutes() {

        $routeMatcher = new RouteMatcher($this->path);

        $routeMatcher->add('assets', '/assets/{file_name}');
        $routeMatcher->add('preview', '/p/{id}');
        $routeMatcher->add('styles', '/styles.css');
        $routeMatcher->add('media', '/media/{file_name}');

        $matchedRoute = $routeMatcher->match();

        if ($matchedRoute) {

            $processor = match($matchedRoute->name) {
                'assets' => AssetsProcessor::class,
                'preview' => PreviewProcessor::class,
                'styles' => StylesProcessor::class,
                'media' => MediaProcessor::class
            };

            $responseObject = (new $processor($this, $matchedRoute))->getResponseObject();

            if ($responseObject) {
                $this->setMatched($responseObject);
            }

        }

    }

    /**
     * These routes can conflict
     * Therefore, if a route matches, and resource was not found, it tries to match other routes again
     */
    private function matchBlogRoutes() {

        $blogRoutesMatcher = new BlogRoutesMatcher($this->blog, $this->path);
        $responseObject = $blogRoutesMatcher->getResponseObject();

        if ($responseObject) {
            $this->setMatched($responseObject);
        }

    }

    private function setMatched(DeliveryAPIResponseObject $responseObject) {
        $this->matched = true;
        $this->responseObject = $responseObject;
    }
    private function matched() {
        return $this->matched;
    }

    public function getResponseObject() {
        if ($this->matched()) {
            return $this->responseObject;
        } else {
            return DeliveryAPIResponseObject::forFile("404", "text/html", 404);
        }
    }

}