<?php

namespace App\Domains\Delivery;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Cache\CacheRepository;
use App\Models\Blog;

class DeliveryRepository
{
    public static function getLaravelResponse(DeliveryAPIResponseObject $obj)
    {
        if ($obj->type === DeliveryAPITypeEnum::FILE) {
            return response($obj->content, $obj->status)
                ->header('Content-Type', $obj->mime_type);
        } elseif ($obj->type === DeliveryAPITypeEnum::REDIRECT) {
            return redirect($obj->to, $obj->status);
        }
    }

    public static function getResponseObject(Blog $blog, string $path): DeliveryAPIResponseObject
    {

        // add leading slash if not
        if (! preg_match('/^\//', $path)) {
            $path = '/' . $path;
        }

        // first, check cache
        if ($blog->type === BlogTypeEnum::DEFAULT) {
            $responseObject = CacheRepository::get($blog, $path);
            if ($responseObject instanceof DeliveryAPIResponseObject) {
                return $responseObject;
            }
        }

        $matcher = new PathMatcher($blog, $path);
        $responseObject = $matcher->getResponseObject();

        if ($blog->type === BlogTypeEnum::DEFAULT) {
            CacheRepository::set($blog, $path, $responseObject);
        }

        return $responseObject;
    }
}
