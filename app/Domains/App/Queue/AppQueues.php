<?php declare(strict_types=1);

namespace App\Domains\App\Queue;

class AppQueues
{

    /**
     * Use this queue for jobs like report generation
     * ex: Link analysis
     */
    public static function reports() : string
    {
        return 'reports';
    }

}