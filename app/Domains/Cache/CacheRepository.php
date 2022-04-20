<?php

namespace App\Domains\Cache;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Models\Blog;
use Illuminate\Support\Facades\Cache;

/**
 * Caches delivery API response objects
 */
class CacheRepository
{
    
    private static function getCacheKeyTag(Blog $blog) : string
    {
        return "blog_cache_{$blog->id}";
    }

    private static function getCacheKey(Blog $blog, string $path) : string
    {
        $tag = self::getCacheKeyTag($blog);
        return $tag . "_$path";
    }
    
    
    public static function set(
        Blog $blog, string $path, 
        DeliveryAPIResponseObject $responseObject
    ) : void
    {
        
        $tag = self::getCacheKeyTag($blog);
        $key = self::getCacheKey($blog, $path);
        Cache::tags($tag)->put($key, serialize($responseObject));
        
    }
    
    public static function get(Blog $blog, string $path) : ?DeliveryAPIResponseObject
    {
        
        $tag = self::getCacheKeyTag($blog);
        $key = self::getCacheKey($blog, $path);
        $cache = Cache::tags($tag)->get($key);
        
        return $cache ? unserialize($cache) : null;
           
    }
    
    public static function clear(Blog $blog, string $path) {
        
        $tag = self::getCacheKeyTag($blog);
        $key = self::getCacheKey($blog, $path);
        Cache::tags($tag)->forget($key);
        
    }
    
    public static function clearAll(Blog $blog) {
        
        $tag = self::getCacheKeyTag($blog);
        Cache::tags($tag)->flush();
        
    }

}
