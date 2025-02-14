<?php

namespace App\Domains\LinkAnalyzer\LinkStatusCheck;

enum StatusCheckType
{

    case INTERNAL; // internal links
    case EXTERNAL; // external links

}