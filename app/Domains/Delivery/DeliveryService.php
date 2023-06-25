<?php declare(strict_types=1);

namespace App\Domains\Delivery;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Cache\CacheService;
use App\Models\Blog;

class DeliveryService
{
    public static function getLaravelResponse(Blog $blog, string $path) : mixed
    {
        $obj = self::getResponseObject($blog, $path);
        if ($obj->type === DeliveryAPITypeEnum::FILE) {
            return response($obj->content, $obj->status)
                ->header('Content-Type', $obj->mime_type)
                ->header('Cache-Control', isset($obj->cache_control) ? $obj->cache_control->value : 'no-cache, private')
                ->header('Access-Control-Allow-Origin', '*');
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

        $shouldUserCache = $blog->type === BlogTypeEnum::DEFAULT && config('app.debug') !== true;

        // first, check cache
        if ($shouldUserCache) {
            $responseObject = app(CacheService::class)->blog($blog)->get($path);
            if ($responseObject instanceof DeliveryAPIResponseObject) {
                return $responseObject;
            }
        }

        $matcher = new PathMatcher($blog, $path);
        $responseObject = $matcher->getResponseObject();

        if ($shouldUserCache) {
            app(CacheService::class)->blog($blog)->set($path, $responseObject);
        }

        return $responseObject;
    }
}
