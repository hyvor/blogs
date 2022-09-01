<?php

namespace App\Domains\App;

use Illuminate\Support\Facades\App;

class DomainService
{
    public static function getAppDomainWithPort()
    {
        $domain = config('blogs.domain_app');
        return App::environment('local') ?
            $domain . ':8080' :
            $domain;
    }
}
