<?php

namespace App\Service\Delivery\Twig;

use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtensions extends AbstractExtension
{
    public function __construct(
        private ThemeFilesService $themeFilesService,
        private TwigLanguage $twigLanguage,
    ) {}

    /** @return TwigFilter[] */
    public function getFilters(): array
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
        ];
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_current_url', [$this, 'isCurrentUrlFunction'], ['needs_context' => true]),
        ];
    }

    /** @param array<mixed> $context */
    public function assetUrlFilter(array $context, string $assetName): string
    {
        $blogCtx = is_array($context['_blog'] ?? null) ? $context['_blog'] : [];
        $baseUrl = is_string($blogCtx['base_url'] ?? null) ? $blogCtx['base_url'] : '';
        return $baseUrl . '/assets/' . $assetName;
    }

    /** @param array<mixed> $context */
    public function assetFilter(array $context, string $assetName): string
    {
        $blogCtx = is_array($context['_blog'] ?? null) ? $context['_blog'] : [];
        $subdomain = is_string($blogCtx['subdomain'] ?? null) ? $blogCtx['subdomain'] : null;
        if ($subdomain === null) {
            return '';
        }

        $blog = $this->twigLanguage->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            return '';
        }

        $file = $this->themeFilesService->getFile($blog, $assetName, ThemeFileFolder::ASSETS);
        return $file?->getContent() ?? '';
    }

    /**
     * @param array<mixed> $context
     * @param mixed[] $args
     */
    public function langFilter(array $context, string $key, array $args = []): ?string
    {
        $blogCtx = is_array($context['_blog'] ?? null) ? $context['_blog'] : [];
        $langCtx = is_array($context['_lang'] ?? null) ? $context['_lang'] : [];
        $subdomain = is_string($blogCtx['subdomain'] ?? null) ? $blogCtx['subdomain'] : null;
        $langCode = is_string($langCtx['code'] ?? null) ? $langCtx['code'] : null;

        if ($subdomain === null || $langCode === null) {
            return null;
        }

        $blog = $this->twigLanguage->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            return null;
        }

        /** @var array<string> $stringArgs */
        $stringArgs = $args;
        return $this->twigLanguage->get($blog, $langCode, $key, $stringArgs);
    }

    /**
     * @param array<mixed> $context
     * @param mixed[] $args
     */
    public function langByNumberFilter(array $context, ?string $value, array $args = []): ?string
    {
        if ($value === null) {
            return null;
        }

        $intValue = (int)$value;
        $zeroKey = is_string($args['zero'] ?? null) ? $args['zero'] : null;
        $oneKey = is_string($args['one'] ?? null) ? $args['one'] : null;
        $multiKey = is_string($args['multi'] ?? null) ? $args['multi'] : null;

        $key = $multiKey;
        if ($intValue === 0) {
            $key = $zeroKey;
        } elseif ($intValue === 1) {
            $key = $oneKey;
        }

        return $this->langFilter($context, (string)$key, [(string)$intValue]);
    }

    /** @param array<mixed> $context */
    public function templateFilter(Environment $env, array $context, string $string): string
    {
        return $env->createTemplate($string)->render($context);
    }

    /** @param array<mixed> $context */
    public function paginationPageUrlFilter(array $context, ?int $pageNumber): string
    {
        $pageNumber ??= 1;
        $metaCtx = is_array($context['_meta'] ?? null) ? $context['_meta'] : [];
        $rawUrl = is_string($metaCtx['url'] ?? null) ? $metaCtx['url'] : '';
        $url = (string)preg_replace('/\/page\/\d+$/', '', $rawUrl);
        $url = rtrim($url, '/');

        if ($pageNumber > 1) {
            $url .= '/page/' . $pageNumber;
        }

        return $url;
    }

    /** @param array<mixed> $context */
    public function isCurrentUrlFunction(array $context, string $url): bool
    {
        $metaCtx = is_array($context['_meta'] ?? null) ? $context['_meta'] : [];
        $blogCtx = is_array($context['_blog'] ?? null) ? $context['_blog'] : [];
        $currentUrl = is_string($metaCtx['url'] ?? null) ? $metaCtx['url'] : '';
        $blogBaseUrl = is_string($blogCtx['base_url'] ?? null) ? $blogCtx['base_url'] : '';

        if (preg_match('/^https?:\/\//', $url)) {
            if (!str_starts_with($url, $blogBaseUrl)) {
                return false;
            }
            if (!str_starts_with($currentUrl, $blogBaseUrl)) {
                return false;
            }
            $urlPath = trim(substr($url, strlen($blogBaseUrl)), '/');
            $currentPath = trim(substr($currentUrl, strlen($blogBaseUrl)), '/');
            return $urlPath === $currentPath;
        }

        $currentPath = trim(substr($currentUrl, strlen($blogBaseUrl)), '/');
        return trim($url, '/') === $currentPath;
    }
}
