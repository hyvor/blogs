<?php

namespace App\Twig; 

use Twig\TwigFilter; 
use Twig\Extension\AbstractExtension;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Dotenv\Dotenv;

use App\Http\Controllers\Delivery\ThemeDeleveryController;
use App\Repositories\DeliveryAPI\Logic\AssetsLogic;

 
class TwigFilters extends AbstractExtension { 


    /*
    * 
    * Twig custom filters array
    * resourses = https://symfony.com/doc/current/templating/twig_extension.html
    *
    */
    public function getFilters()
    {
        return [
            new TwigFilter('assets', [$this, 'assetsFilter']),
            new TwigFilter('lang', [$this, 'langFilter']),
        ];
    }


    /*
    *
    * assets
    * Twig custom assets filter
    *
    */
    public function assetsFilter($url): string
    {
        $domain = request()->getSchemeAndHttpHost();

        $extentionFilter = pathinfo($url, PATHINFO_EXTENSION);
        $extention = '.'.$extentionFilter;

        // return $extention;
        if($extention == '.css')
        {
            return $domain.'/assets/'.$url;
        }
        else if ($extention == '.js') 
        {
            return $domain.'/assets/'.$url.'?23423';
        }
        if($extention == '.svg')
        {
            return $domain.'/assets/'.$url;
        }
        else if ($extention == '.jpeg') 
        {
            return $domain.'/assets/'.$url;
        }
        if($extention == '.png')
        {
            return $domain.'/assets/'.$url;
        }
        else if ($extention == '.gif') 
        {
            return $domain.'/assets/'.$url;
        }
        else
        {
            return 'broken Link';
        }

        // If we dont need the 'Broken Link' comment we can use this.
        // return $domain.'/assets/'.$url;
    }

    /*
    *
    * lang
    * Twig custom language filter
    *
    */
    public function langFilter($lang): string
    {
        $dotenv = Dotenv::createImmutable(public_path('themes'));
        $dotenv->load(); 

        // In laravel getenv() function is not working Insted of that you should use the env() file
        $selectedLanguage = env('LANGUAGE');

        $translator = new GoogleTranslate('en') ;
        $result = $translator->setSource('en')
                     ->setTarget($selectedLanguage)
                     ->translate($lang);
                           
        return $result;
    }

}