<?php declare(strict_types=1);

namespace App\Domains\App;

use Illuminate\Support\Facades\App;

class DomainService
{
    public static function getAppDomainWithPort() : string
    {
        return strval(config('blogs.domain_app'));
    }

    public static function getAppUrl() : string
    {
        $protocol = App::environment('local') ? 'http://' : 'https://';
        return $protocol . self::getAppDomainWithPort();
    }

}
