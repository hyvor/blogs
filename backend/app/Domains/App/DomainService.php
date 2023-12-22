<?php

namespace App\Domains\App;

use Illuminate\Support\Facades\App;

class DomainService
{
    public static function getAppDomainWithPort() : string
    {
        $domain = strval(config('blogs.domain_app'));
        return App::environment('local') ?
            $domain . ':8080' :
            $domain;
    }

    public static function getAppUrl() : string
    {
        $protocol = App::environment('local') ? 'http://' : 'https://';
        return $protocol . self::getAppDomainWithPort();
    }

}
