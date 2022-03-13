<?php
namespace App\Domains\Delivery;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\BlogTheme\Feed;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\Route;
use App\Models\Tag;
use App\Models\User;

class RouteProcessor {

    public Blog $blog;
    public MatchedRoute $matchedRoute;
    public Language $language;

    public ?string $filter;

    private ?DeliveryAPIResponseObject $responseObject = null;

    public function __construct(Blog $blog,  MatchedRoute $matchedRoute, Language $language) 
    {
        $this->blog = $blog;
        $this->matchedRoute = $matchedRoute;
        $this->language = $language;

        $this->setFilterQ();


        $this->renderFeed() ||
        $this->renderPage();
    }

    private function setFilterQ() 
    {

        if ($this->matchedRoute->route->posts_filter === null) {
            $this->filter = null;
        } else {

            /**
             * Replace {slug} in posts_filter with the matched route params
             */
            $this->filter = preg_replace_callback('/\{(.+)\}/', function($matches) {
        
                $var = $matches[1];
                $param = $this->matchedRoute->param($var) ?? '';

                return "'$param'";

            }, $this->matchedRoute->route->posts_filter);
        }
    
    }

    private function renderFeed() : bool 
    {
        if (
            $this->matchedRoute->route->posts_filter !== null &&
            $this->matchedRoute->param('suffix') === 'feed'
        ) {

            Feed::generateFeed($this->blog, $this->filter);
            return true;

        }

        return false;
    }

    private function renderPage()
    {

        $renderer = new TemplateRenderer(
            $this->blog, $this->matchedRoute, $this->language,
            $this->filter
        );
        $this->responseObject = $renderer->getResponseObject();

    }

    public function getResponseObject() {
        return $this->responseObject;
    }
    

}