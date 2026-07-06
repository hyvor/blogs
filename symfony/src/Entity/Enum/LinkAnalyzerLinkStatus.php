<?php

namespace App\Entity\Enum;

use App\Service\LinkAnalysis\LinkAnalysisService;

enum LinkAnalyzerLinkStatus: string
{
    case OK = 'ok';
    case BROKEN = 'broken';
    case RISKY = 'risky';
    case REDIRECT = 'redirect';
    case IGNORED = 'ignored';

    public static function fromStatus(int $status): self
    {
        if ($status === LinkAnalysisService::IGNORE_CODE) {
            return self::IGNORED;
        } elseif ($status >= 200 && $status < 300) {
            return self::OK;
        } elseif ($status >= 300 && $status < 400) {
            return self::REDIRECT;
        } elseif ($status === 404 || $status === 0) {
            return self::BROKEN;
        }
        return self::RISKY;
    }
}
