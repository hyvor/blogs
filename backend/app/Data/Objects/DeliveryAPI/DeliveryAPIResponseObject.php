<?php declare(strict_types=1);

namespace App\Data\Objects\DeliveryAPI;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\RedirectTypeEnum;

class DeliveryAPIResponseObject
{
    public DeliveryAPITypeEnum $type;

    public int $at;

    public bool $cache;

    public DeliveryAPICacheControlHeaderEnum $cache_control;

    public int $status;

    // for file
    public DeliveryAPIFileTypeEnum $file_type;

    public string $content;

    public string $mime_type;

    // for redirect
    public string $to;

    public function __construct(DeliveryAPITypeEnum $type)
    {
        $this->type = $type;
        $this->at = now()->getTimestamp();
    }

    /**
     * Files content is base64 encoded
     */
    public static function forFile(
        DeliveryAPIFileTypeEnum $type,
        string $content,
        string $mimeType = 'text/html',
        bool $cache = true,
        int $status = 200,
        DeliveryAPICacheControlHeaderEnum $browserCache = DeliveryAPICacheControlHeaderEnum::NO_CACHE
    ) : self
    {
        $obj = new self(DeliveryAPITypeEnum::FILE);
        $obj->file_type = $type;
        $obj->content = $content;
        $obj->mime_type = $mimeType;
        $obj->cache = $cache;
        $obj->cache_control = $browserCache;
        $obj->status = $status;

        return $obj;
    }

    public static function forRedirect(string $to, RedirectTypeEnum $type)
    {
        $obj = new self(DeliveryAPITypeEnum::REDIRECT);
        $obj->to = $to;
        $obj->cache = true;
        $obj->status = $type === RedirectTypeEnum::PERMANENT ? 301 : 302;

        return $obj;
    }

    /*// for caching
    public static function fromArray(array $arr)
    {
        $obj = new self(DeliveryAPITypeEnum::from($arr['type']));
        $obj->status = $arr['status'];

        if (isset($arr['content'])) {
            $obj->content = $arr['content'];
            $obj->mime_type = $arr['mime_type'];
        }

        if (isset($arr['to'])) {
            $obj->to = $arr['to'];
        }
    }*/
}
