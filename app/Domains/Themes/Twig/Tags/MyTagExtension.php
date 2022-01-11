<?php
namespace App\Domains\Themes\Twig\Tags; 

// use Twig\Extension\Twig_Extension;
// namespace Drupal\twig_extension\TwigExtension;

use Twig\Extension\AbstractExtension;
use App\Domains\Themes\Twig\Tags\TokenParser\TwigTokenParser;




class MyTagExtension extends AbstractExtension
{

   public function getTokenParsers()
   {
      // return array ( new TwigTokenParser(),);

      return [new TwigTokenParser()];

   }

   // public function getName()
   // {
   //    return 'hyvorTag';
   // }

}