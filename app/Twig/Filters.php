<?php
 
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Filters extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('count_words', [$this, 'countWords']),
        ];
    }
 
    public static function countWords($sentence)
    {
      return count(explode(' ', $sentence));
    }
}