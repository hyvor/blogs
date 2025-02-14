<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

enum StatusCheckType: string
{

    case INTERNAL = 'internal'; // internal links
    case EXTERNAL = 'external'; // external links

}