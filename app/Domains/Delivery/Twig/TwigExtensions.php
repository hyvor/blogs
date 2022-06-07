<?php

namespace App\Domains\Delivery\Twig;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogRepository;
use App\Domains\Language\LanguageRepository;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Exceptions\TrustedException;
use App\Helpers\InternalAPICaller;
use Hyvor\SvgIcons\Exception\SvgIconException;
use Hyvor\SvgIcons\Icon;
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

    public function getFilters()
    {
        return [
            new TwigFilter('asset_url', [$this, 'assetUrlFilter'], ['needs_context' => true]),
            new TwigFilter('asset', [$this, 'assetFilter'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFilter('lang', [$this, 'langFilter'], ['needs_context' => true, 'is_variadic' => true]),
            new TwigFilter('lang_by_number', [$this, 'langByNumberFilter'], ['needs_context' => true, 'is_variadic' => true]),
            new TwigFilter('template', [$this, 'templateFilter'], [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFilter('pagination_page_url', [$this, 'paginationPageUrlFilter'], ['needs_context' => true]),
            new TwigFilter('language_variant_url', [$this, 'languageVariantUrlFilter'], [
                'needs_context' => true,
            ]),
        ];
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('data', [$this, 'dataFunction'], [
                'needs_context' => true,
                'is_variadic' => true,
            ]),
            new TwigFunction('icon', [$this, 'iconFunction'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('is_current_url', [$this, 'isCurrentUrlFunction'], [
                'needs_context' => true,
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

        return $context['_blog']['base_url'] . '/assets/' . $assetName;
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
        $currentLanguage = LanguageRepository::getLanguageByCode($blog, $context['_lang']['code']);

        if (! isset($this->twigLanguageHandler)) {
            $this->twigLanguageHandler = new TwigLanguage($blog, $currentLanguage);
        }

        return $this->twigLanguageHandler->get($key, $args);
    }

    public function langByNumberFilter($context, $value, array $args = [])
    {
        $zero = $args['zero'] ?? null;
        $one = $args['one'] ?? null;
        $multi = $args['multi'] ?? null;

        $value = (int) $value;

        $key = $multi;
        if ($value === 0) {
            $key = $zero;
        } elseif ($value === 1) {
            $key = $one;
        }

        return $this->langFilter($context, $key, [$value]);
    }

    public function templateFilter(\Twig\Environment $env, $context, $string)
    {
        $template = $env->createTemplate($string);
        $html = $template->render($context);

        return $html;
    }

    public function paginationPageUrlFilter($context, ?int $pageNumber)
    {
        $pageNumber ??= 1;

        $url = $context['_meta']['url'];
        $url = preg_replace('/\/page\/\d+$/', '', $url);

        $url = rtrim($url, '/');
        if ($pageNumber > 1) {
            $url .= '/page/' . $pageNumber;
        }

        return $url;
    }

    public function languageVariantUrlFilter($context, string $languageCode): string
    {
        $route = $context['_route'];

        if (
            $route === 'post' || $route === 'page' ||
            $route === 'tag' || $route === 'author'
        ) {
            $object = match ($route) {
                'post', 'page' => $context['_post'],
                'tag' => $context['_tag'],
                'author' => $context['_author']
            };

            if ($object['language']['code'] === $languageCode) {
                return $object['url'];
            }

            $variants = $object['variants'];

            foreach ($variants as $variant) {
                if ($variant['language']['code'] === $languageCode) {
                    return $variant['url'];
                }
            }
        }

        $language = collect($context['_blog']['languages'])->firstWhere('code', $languageCode);

        if (! $language) {
            return ''; // language not found?
        }


        $blog = $this->getBlogFromContext($context);

        return PermalinkRepository::getBlogPermalink(
            $blog,
            LanguageRepository::getLanguageByCode($blog, $language['code'])
        );

    }


    public function dataFunction($context, array $params = [])
    {
        $blog = $this->getBlogFromContext($context);

        $endpoint = $params['endpoint'] ?? null;

        if (! $endpoint) {
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

    public function iconFunction($library, $iconName, $width = null, $height = null): string
    {
        try {
            $icon = new Icon($library, $iconName);

            return $icon->getSvg($width, $height);
        } catch (SvgIconException) {
            return '';
        }
    }

    // checks if a given URL is the current one
    public function isCurrentUrlFunction($context, string $url): bool
    {
        $currentUrl = $context['_meta']['url'];
        $blogBaseUrl = $context['_blog']['base_url'];

        $currentPath = substr($currentUrl, strlen($blogBaseUrl));
        $currentPath = trim($currentPath, '/');

        // absolute URL
        if (preg_match('/^https?:\/\//', $url)) {
            // if not starting with the blog base URL, it is not the current one
            if (! substr($url, 0, strlen($blogBaseUrl)) == $blogBaseUrl) {
                return false;
            } else {
                $path = substr($url, strlen($blogBaseUrl));
                $path = trim($path, '/');

                return $path === $currentPath;
            }
        } else {
            return trim($url, '/') === $currentPath;
        }
    }

    private function getBlogFromContext($context)
    {
        if (! isset($this->blog)) {
            $subdomain = $context['_blog']['subdomain'];
            $this->blog = BlogRepository::getBlogBySubdomain($subdomain);
        }

        return $this->blog;
    }
}
