<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\BlogTheme\BlogThemeRepository;
use Illuminate\Http\Request;
use App\Helpers\MimeTypes;
use App\Models\Blog;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Enums\DeliveryAPIScopeEnum;
use App\Domains\BlogTheme\BlogThemeTemplateRepository;
use App\Domains\Delivery\DeliveryRepository;
use App\Domains\Post\PostRepository;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DeliveryAPIController
{
    public function handle(Request $request, Blog $blog)
    {
        /**
         * Delivery API says "how to serve a path"
         *
         * Takes two inputs:
         *  subdomain
         *  path
         *
         * Returns an output as specified [here]()
         */

        $response = DeliveryRepository::getHtml(
            $blog,
            $request->route('path') ?? '',
            [
                'page' => $request->input('page')
            ]
        );

        return $response ? response()->json($response) : self::notFound();
    }

    private static function notFound()
    {
        return response()->json(DeliveryAPIResponseObject::forFile('404', 'text/html', 404));
    }
}
