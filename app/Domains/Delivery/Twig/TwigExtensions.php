<?php
namespace App\Domains\Delivery\Twig;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogRepository;
use App\Domains\LocalDev\LocalDevRepository;
use App\Domains\ThemeFiles\ThemeFilesRepository;
use App\Domains\Language\LanguageRepository;
use App\Domains\Route\PermalinkRepository;
use App\Exceptions\TrustedException;
use App\Helpers\InternalAPICaller;
use App\Models\Blog;
use App\Models\LocalDev;
use League\Flysystem\Adapter\Local;
use Twig\Error\Error;
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
    public Blog|LocalDev $themable;

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
            ]),
            new TwigFilter('pagination_page_url', [$this, 'paginationPageUrlFilter'], ['needs_context' => true])
        ];

    }


    public function getFunctions()
    {

        return [
            new TwigFunction('data', [$this, 'dataFunction'], [
                'needs_context' => true,
                'is_variadic' => true
            ]),
        ];

    }

    public function assetUrlFilter($context, $assetName)
    {

        /**
         * Here, we cannot use PermalinkRepository 
         * because it requires Blog $blog, which has a wrong base URL
         * when taken through ->getBlogFromContext for LocalDev requests
         * So, we simple use the BlogObject 
         */
        
        return $context['_blog']['url'] . '/assets/' . $assetName;

    }

    public function assetFilter($context, $assetName)
    {

        $themable = $this->getThemableFromContext($context);
        $file = ThemeFilesRepository::getFile($themable, $assetName, ThemeFileFolderEnum::ASSETS);

        return $file?->content ?? "";

    }

    public function langFilter($context, $key, array $args = [])
    {

        $blog = $this->getBlogFromContext($context);
        $themable = $this->getThemableFromContext($context);
        $currentLanguage = LanguageRepository::getLanguageByCode($blog, $context['_lang']);

        if (!isset($this->twigLanguageHandler)) {
            $this->twigLanguageHandler = new TwigLanguage($blog, $themable, $currentLanguage);
        }

        return $this->twigLanguageHandler->get($key, $args);

    }

    public function templateFilter(\Twig\Environment $env, $context, $string)
    {

        $template = $env->createTemplate($string);
        $html = $template->render($context);
        return $html;

    }
    
    public function paginationPageUrlFilter($context, int $pageNumber)
    {
        
        $url = $context['_meta']['url'];
        $url = preg_replace('/\/page\/\d+$/', '', $url);
        
        $url = rtrim($url, '/');
        if ($pageNumber > 1) {
            $url .= '/page/' . $pageNumber;
        }
        
        return $url;
        
    }


    public function dataFunction($context, array $params = [])
    {

        $blog = $this->getBlogFromContext($context);
        
        $endpoint = $params['endpoint'] ?? null;
        
        if (!$endpoint) {
            throw new Error('endpoint is required for the data() function');
        }
        
        unset($params['endpoint']);
        
        try {
            $response = InternalAPICaller::data($blog->subdomain, $endpoint, $params);
        } catch (TrustedException $e) {
            // throw twig error
            throw new Error("Error when calling the Data API  /$endpoint endpoint: " . $e->getMessage());
        }
        
        return $response;

    }


    private function getBlogFromContext($context)
    {

        if (!isset($this->blog)) {
            $subdomain = $context['_blog']['subdomain'];
            $this->blog = BlogRepository::getBlogBySubdomain($subdomain);
        }

        return $this->blog;

    }
    
    private function getThemableFromContext($context) : Blog|LocalDev
    {
        
        if (!isset($this->themable)) {
            
            if (isset($context['_local_dev_uuid'])) {
                $this->themable = LocalDevRepository::getLocalDevByUUID($context['_local_dev_uuid']);
            } else {
                $this->themable = BlogRepository::getBlogBySubdomain($context['_blog']['subdomain']);
            }
            
        }
        
        return $this->themable;
    }
    

}
