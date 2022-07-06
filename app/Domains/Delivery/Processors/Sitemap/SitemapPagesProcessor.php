<?php
namespace App\Domains\Delivery\Processors\Sitemap;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Delivery\Processors\RouteProcessorAbstract;
use App\Domains\Delivery\RouteMatcher\MatchedRoute;
use App\Domains\Post\PostRepository;
use App\Models\Blog;

class SitemapPagesProcessor extends RouteProcessorAbstract
{

    private Blog $blog;

    public function __construct(PathMatcher $pathMatcher, MatchedRoute $matchedRoute)
    {

        $this->blog = $pathMatcher->blog;

        $pagesXML = $this->pagesXML();
        $indexXML = $this->indexXML();

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            $indexXML
            $pagesXML
        </urlset>
        XML;

        $this->setResponseObject(
            DeliveryAPIResponseObject::forFile(
                DeliveryAPIFileTypeEnum::TEMPLATE,
                $xml,
                'text/xml',
            )
        );

    }

    private function pagesXML() : string
    {
        return PostRepository::getPages($this->blog)
            ->mapInto(PostEntry::class)
            ->map(fn ($entry) => $entry->toString())
            ->implode("\n");
    }

    private function indexXML() : string
    {

    }


}