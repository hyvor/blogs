<?php

namespace App\Data\Objects\DeliveryAPI;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\RedirectTypeEnum;

class DeliveryAPIResponseObject
{
    public DeliveryAPITypeEnum $type;

    // for file
    public string $content;
    public string $mime_type;

    // for redirect
    public string $to;

    // for both
    public int $status;

    public function __construct(DeliveryAPITypeEnum $type)
    {
        $this->type = $type;
    }

    /**
     * Files content is base64 encoded
     */
    public static function forFile(string $content, string $mimeType, int $status = 200)
    {
        $obj = new self(DeliveryAPITypeEnum::FILE);
        $obj->content = base64_encode($content);
        $obj->mime_type = $mimeType;
        $obj->status = $status;

        return $obj;
    }


    public static function forRedirect(RedirectTypeEnum $type, string $to)
    {
        $obj = new self(DeliveryAPITypeEnum::REDIRECT);
        $obj->both = $type->value;
        $obj->to = $to;

        return $obj;
    }
}
