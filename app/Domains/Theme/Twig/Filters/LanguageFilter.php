<?php

namespace App\Domains\Themes\Twig\Filters; 

use Twig\TwigFilter; 
use Twig\Extension\AbstractExtension;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Dotenv\Dotenv;

use App\Http\Controllers\Delivery\ThemeDeleveryController;
use App\Repositories\DeliveryAPI\Logic\AssetsLogic;

 
class LanguageFilter extends AbstractExtension { 
    /*
    * 
    * Twig custom filters array
    * resourses = https://symfony.com/doc/current/templating/twig_extension.html
    *
    */
    public function getFilters()
    {
        return [
            new TwigFilter('lang', [$this, 'langFilter']),
        ];
    } 


    /*
    *
    * Twig custom language filter
    * https://www.skillsugar.com/get-filename-without-extension-in-phplaravel 
    *
    */
    public function langFilter($lang): string
    {
        $mainEnv = file_get_contents('themes/.env');
        $commonLanguage = Dotenv::parse($mainEnv);

        // $dotenv = new Dotenv();
        // $dotenv->load('themes'.'/.env');

        $file_name = pathinfo('de.env', PATHINFO_FILENAME);

        if($commonLanguage['LANGUAGE'] == $file_name)
        {
            $languageArray = file_get_contents('themes/language/en.env');
            $finalLanguage = Dotenv::parse($languageArray);
            return $finalLanguage[$lang];
        }

        // $translator = new GoogleTranslate('en');
        // $result = $translator->setSource('en')
        //              ->setTarget($selectedLanguage)
        //              ->translate($lang);
                           
        return 'Its not supported by the deleloper';
    }

}