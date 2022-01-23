<?php

namespace App\Domains\Theme;

use App\Domains\Theme\ThemeRepository;
use App\Domains\Theme\Types\OutPutDeliveryAPI;
use ScssPhp\ScssPhp\Compiler;
use Twig\Environment;

class TemplateRepository
{
    /*
    *
    *
    * This is the index page of the bolg
    *
    *
    */
    public static function index()
    {
        $fileName = ThemeRepository::deliverThemeData();

        foreach ($fileName as $homePage) {
            if ($homePage['name'] == 'index.twig') {
                $homeContent = $homePage['content'];

                $loader = new \Twig\Loader\ArrayLoader(array(
                    'index.html' => $homeContent,
                ));
                $twig = new \Twig\Environment($loader);

                $fileContent =  $twig->render(
                    'index.html',
                    array(
                        'name' => 'Finnaly done',
                        'occupation' => 'must get the approvel',
                    )
                );

                $getContent = array(
                        'type' => 'text',
                        'mime_type' => 'text/html',
                        'content' => $fileContent
                    );
                return OutPutDeliveryAPI::renderContent($getContent);
            }
        }
    }

    /*
    *
    *
    * This is the AUTHOR page of the bolg
    *
    *
    */
    public static function author()
    {

        $authorExist = ThemeRepository::checkAuthor();

        if ($authorExist == null) {
            $homePage = ThemeRepository::deliverThemeData();

            foreach ($homePage as $getHomePage) {
                if ($getHomePage['name'] == 'index.twig') {
                    $homeContent = $getHomePage['content'];

                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'index.html' => $homeContent,
                    ));
                    $twig = new \Twig\Environment($loader);

                    $fileContent =  $twig->render(
                        'index.html',
                        array(
                            // 'style' => $stylecss ,
                            'name' => 'Finnaly done',
                            'occupation' => 'must get the approvel',
                            // 'script' => $script
                        )
                    );
                    $getContent = array(
                            'type' => 'text',
                            'mime_type' => 'text/html',
                            'content' => $fileContent
                        );
                    return OutPutDeliveryAPI::renderContent($getContent);
                }
            }
        } else {
            $authorPage = ThemeRepository::deliverAuthorData();

            foreach ($authorPage as $getauthorPage) {
                if ($getauthorPage['name'] == 'author.twig') {
                    $authorContent = $getauthorPage['content'];

                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'author.html' => $authorContent,
                    ));
                    $twig = new \Twig\Environment($loader);

                    $fileContent =  $twig->render(
                        'author.html',
                        array(
                            // 'style' => $stylecss ,
                            'name' => 'Finnaly done',
                            'occupation' => 'must get the approvel',
                            // 'script' => $script
                        )
                    );
                    $getContent = array(
                            'type' => 'text',
                            'mime_type' => 'text/html',
                            'content' => $fileContent
                        );
                    return OutPutDeliveryAPI::renderContent($getContent);
                }
            }
        }
    }

    /*
    *
    *
    * This is the tags page of the bolg
    *
    *
    */
    public static function tag()
    {
        $tagExist = ThemeRepository::checkTag();

        if ($tagExist == null) {
            $homePage = ThemeRepository::deliverThemeData();
            foreach ($homePage as $getHomePage) {
                if ($getHomePage['name'] == 'index.twig') {
                    $homeContent = $getHomePage['content'];

                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'index.html' => $homeContent,
                    ));
                    $twig = new \Twig\Environment($loader);

                    $fileContent =  $twig->render(
                        'index.html',
                        array(
                                    // 'style' => $stylecss ,
                                    'name' => 'Finnaly done',
                                    'occupation' => 'must get the approvel',
                                    // 'script' => $script
                        )
                    );
                    $getContent = array(
                            'type' => 'text',
                            'mime_type' => 'text/html',
                            'content' => $fileContent
                        );
                    return OutPutDeliveryAPI::renderContent($getContent);
                }
            }
        } else {
            $tagPage = ThemeRepository::deliverTagData();
            foreach ($tagPage as $getTagPage) {
                if ($getTagPage['name'] == 'index.twig') {
                    $tagContent = $getTagPage['content'];

                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'index.html' => $tagContent,
                    ));
                    $twig = new \Twig\Environment($loader);

                    $fileContent =  $twig->render(
                        'index.html',
                        array(
                                    // 'style' => $stylecss ,
                                    'name' => 'Finnaly done',
                                    'occupation' => 'must get the approvel',
                                    // 'script' => $script
                        )
                    );
                    $getContent = array(
                            'type' => 'text',
                            'mime_type' => 'text/html',
                            'content' => $fileContent
                        );
                    return OutPutDeliveryAPI::renderContent($getContent);
                }
            }
        }
    }


    /*
    *
    * This is the posts & pages of the bolg
    *
    */
    public static function pages()
    {
        // $type = 1; //check page, post or redirect
        $type = ThemeRepository::isPage();
        // dd($type);

        if ($type == 1) {
            dd('this is a page');
        } elseif ($type == 0) {
            $fileName = ThemeRepository::deliverSinglePage();

            foreach ($fileName as $singlePage) {
                if ($singlePage['name'] == 'single.twig') {
                    $singleContent = $singlePage['content'];
                    $loader = new \Twig\Loader\ArrayLoader(array(
                        'single.html' => $singleContent,
                    ));
                    $twig = new \Twig\Environment($loader);

                    $fileContent = $twig->render(
                        'single.html',
                        array(
                                'Title' => "hyvor blog",
                                'name' => 'hyvor' ,
                                'number' => "123456789",
                                'test' => 'loader',
                        )
                    );

                    $getContent = array(
                        'type' => 'text',
                        'mime_type' => 'text/html',
                        'content' => $fileContent
                    );
                    return OutPutDeliveryAPI::renderContent($getContent);
                }
            }
        } else {
            dd('this is an redirect');
        }
    }
}
