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
    public static function forFile(string $content, string $mimeType = 'text/html', int $status = 200)
    {
        $obj = new self(DeliveryAPITypeEnum::FILE);
        $obj->content = $content;
        $obj->mime_type = $mimeType;
        $obj->status = $status;

        return $obj;
    }


    public static function forRedirect(string $to, RedirectTypeEnum $type)
    {
        $obj = new self(DeliveryAPITypeEnum::REDIRECT);
        $obj->to = $to;
        $obj->status = $type->value;

        return $obj;
    }

    // for caching
    public static function fromArray(array $arr) {

        $obj = new self( DeliveryAPITypeEnum::from($arr['type']) );
        $obj->status = $arr['status'];

        if (isset($arr['content'])) {
            $obj->content = $arr['content'];
            $obj->mime_type = $arr['mime_type'];
        }

        if (isset($arr['to'])) {
            $obj->to = $arr['to'];
        }

    }
}
