<?php
namespace App\Data\Objects\DeliveryAPI;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\RedirectTypeEnum;

class DeliveryAPIObject {

    public DeliveryAPITypeEnum $type;

    // for file
    public string $content;
    public string $mime_type;

    // for redirect
    public int $code;
    public string $to;

    public function __construct(DeliveryAPITypeEnum $type) {
        $this->type = $type;
    }

    /**
     * Files content is base64 encoded
     */
    static function forFile(string $content, string $mimeType) {
        $obj = new self(DeliveryAPITypeEnum::FILE);
        $obj->content = base64_encode($content);
        $obj->mime_type = $mimeType;

        return $obj;
    }

    
    static function forRedirect(RedirectTypeEnum $type, string $to) {
        $obj = new self(DeliveryAPITypeEnum::REDIRECT);
        $obj->code = $type->value;
        $obj->to = $to;

        return $obj;
    }

    static function forNotFound() {
        return new self(DeliveryAPITypeEnum::NOTFOUND);
    }

}
