<?php
namespace App\Types\DeliveryAPI;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\RedirectTypeEnum;

class DeliveryAPIObject {

    public DeliveryAPITypeEnum $type;

    // for text and binary
    public string $content;
    public string $mime_type;

    // for redirect
    public int $code;
    public string $to;

    public function __construct(DeliveryAPITypeEnum $type) {
        $this->type = $type;
    }

    static function forTextOrBinary(string $content, string $mimeType, bool $isBinary) {
        $obj = new self($isBinary ? DeliveryAPITypeEnum::BINARY : DeliveryAPITypeEnum::TEXT);
        $obj->content = $content;
        $obj->mime_type = $mimeType;

        return $obj;
    }
    static function forText(string $content, string $mimeType) {
        return self::forTextOrBinary($content, $mimeType, false);
    }
    static function forBinary(string $content, string $mimeType) {
        return self::forTextOrBinary($content, $mimeType, true);
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
