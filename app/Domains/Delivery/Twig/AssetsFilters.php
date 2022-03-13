<?php

namespace App\Domains\Delivery\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use App\Http\Controllers\DeliveryAPI\DeliveryAPIController;
use App\Domains\Theme\AssetsRepository;

class AssetsFilters extends AbstractExtension
{
    /*
    *
    * Twig custom filters array
    * resourses = https://symfony.com/doc/current/templating/twig_extension.html
    *
    */
    public function getFilters()
    {
        return [
            new TwigFilter('assets', [$this, 'assetsFilter']),
        ];
    }


    /*
    *
    * assets
    * Twig custom assets filter
    *
    */
    public function assetsFilter($url): string
    {
        $domain = request()->getSchemeAndHttpHost();

        // If we dont need the 'Broken Link' comment we can use this.
        return $domain . '/assets/' . $url;
    }
}
