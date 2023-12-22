<?php declare(strict_types=1);

namespace App\Data\Enums;

enum LinkAnalysisEmailReportEnum : string
{

    case NEVER = 'never';
    // when broken links are found
    case BROKEN = 'broken';
    case ALWAYS = 'always';

}
