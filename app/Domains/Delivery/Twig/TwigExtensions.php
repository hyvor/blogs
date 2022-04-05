<?php
namespace App\Domains\Delivery\Twig;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogRepository;
use App\Domains\ThemeFiles\ThemeFilesRepository;
use App\Domains\Language\LanguageRepository;
use App\Domains\Route\PermalinkRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Defines three filters
 *     
 *  asset_url - 
 *  asset
 *  lang
 *  
 * 
 * And one function
 *  data - to call the Data API
 */


class TwigExtensions extends AbstractExtension
{

    // to prevent duplicate queries
    public $blog;
    public $twigLanguageHandler;

    public function getFilters()
    {
        
        return [
            new TwigFilter('asset_url', [$this, 'assetUrlFilter'], ['needs_context' => true]),
            new TwigFilter('asset', [$this, 'assetFilter'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFilter('lang', [$this, 'langFilter'], ['needs_context' => true, 'is_variadic' => true]),
            new TwigFilter('template', [$this, 'templateFilter'], [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html']
            ])
        ];

    }


    public function getFunctions()
    {

        return [
            new TwigFunction('data', [$this, 'dataFunction']),
        ];

    }

    public function assetUrlFilter($context, $assetName)
    {

        $blog = $this->getBlogFromContext($context);
        return PermalinkRepository::getAssetPermalink($assetName, $blog);

    }

    public function assetFilter($context, $assetName)
    {

        $blog = $this->getBlogFromContext($context);
        $file = ThemeFilesRepository::getFile($blog, $assetName, ThemeFileFolderEnum::ASSETS);

        return $file?->content ?? "";

    }

    public function langFilter($context, $key, array $args = [])
    {

        $blog = $this->getBlogFromContext($context);
        $currentLanguage = LanguageRepository::getLanguageByCode($blog, $context['_lang']);

        if (!isset($this->twigLanguageHandler)) {
            $this->twigLanguageHandler = new TwigLanguage($blog, $currentLanguage);
        }

        return $this->twigLanguageHandler->get($key, $args);

    }

    public function templateFilter(\Twig\Environment $env, $context, $string)
    {

        $template = $env->createTemplate($string);
        $html = $template->render($context);
        return $html;

    }


    public function dataFunction()
    {

        return null;

    }


    private function getBlogFromContext($context)
    {

        if (!isset($this->blog)) {
            $subdomain = $context['_blog']['subdomain'];
            $this->blog = BlogRepository::getBlogBySubdomain($subdomain);
        }

        return $this->blog;

    }
    

}