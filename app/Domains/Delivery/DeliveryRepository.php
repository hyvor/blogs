<?php
namespace App\Domains\Delivery;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Models\Blog;
use App\Models\LocalDev;

class DeliveryRepository {

    public static function getLaravelResponse(DeliveryAPIResponseObject $obj) {

        if ($obj->type === DeliveryAPITypeEnum::FILE) {

            return response($obj->content, $obj->status)
                ->header('Content-Type', $obj->mime_type);

        } elseif ($obj->type === DeliveryAPITypeEnum::REDIRECT) {

            return redirect($obj->to, $obj->status);

        }
    }

    public static function getResponseObject (
        Blog $blog, string $path, 
        LocalDev $localDev = null) : DeliveryAPIResponseObject 
    {
        
        $matcher = new PathMatcher($blog, $path, $localDev);
        return $matcher->getResponseObject();

    }

}