<?php

namespace App\Entity\Enum\Blog;

enum LinkAnalysisEmailReport: string
{
    case NEVER = 'never';
    case BROKEN = 'broken';
    case ALWAYS = 'always';
}
