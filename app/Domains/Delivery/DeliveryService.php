<?php

namespace App\Domains\Delivery;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Cache\CacheService;
use App\Models\Blog;

class DeliveryService
{
    public static function getLaravelResponse(DeliveryAPIResponseObject $obj)
    {
        if ($obj->type === DeliveryAPITypeEnum::FILE) {
            return response($obj->content, $obj->status)->header('Content-Type', $obj->mime_type);
        } elseif ($obj->type === DeliveryAPITypeEnum::REDIRECT) {
            return redirect($obj->to, $obj->status);
        }
    }

    public static function getResponseObject(Blog $blog, string $path): DeliveryAPIResponseObject
    {

        // add leading slash if not
        if (! preg_match('/^\//', $path)) {
            $path = '/'.$path;
        }

        // first, check cache
        if ($blog->type === BlogTypeEnum::DEFAULT) {
            $responseObject = app(CacheService::class)->blog($blog)->get($blog, $path);
            if ($responseObject instanceof DeliveryAPIResponseObject) {
                return $responseObject;
            }
        }

        $matcher = new PathMatcher($blog, $path);
        $responseObject = $matcher->getResponseObject();

        if ($blog->type === BlogTypeEnum::DEFAULT && config('app.debug') !== true) {
            app(CacheService::class)->blog($blog)->set($path, $responseObject);
        }

        return $responseObject;
    }
}
