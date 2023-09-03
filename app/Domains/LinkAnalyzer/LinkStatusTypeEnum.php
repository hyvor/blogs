<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

enum LinkStatusTypeEnum : string
{

    case OK = 'ok';
    case BROKEN = 'broken';
    case REDIRECT = 'redirect';
    case IGNORED = 'ignored';

    public static function fromStatus(int $status) : self
    {

        if ($status === LinkAnalyzeService::IGNORE_CODE) {
            return self::IGNORED;
        } else if ($status >= 200 && $status < 300) {
            return self::OK;
        } else if ($status >= 300 && $status < 400) {
            return self::REDIRECT;
        } else {
            return self::BROKEN;
        }

    }

}
