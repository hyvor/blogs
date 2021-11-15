<?php
 
namespace App\Twig;
 
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
 
class Functions extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('now', [$this, 'now']),
        ];
    }
 
    public static function now()
    {
      return date('d/m/Y H:i:s');
    }

}