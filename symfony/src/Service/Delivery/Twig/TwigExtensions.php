<?php

namespace App\Service\Delivery\Twig;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Blog\BlogService;
use App\Service\Delivery\Twig\Toc\TocHeading;
use App\Service\Delivery\Twig\Toc\TocHtml;
use App\Service\Language\LanguageService;
use App\Service\Route\PermalinkService;
use App\Service\Theme\ThemeFilesService;
use Hyvor\SvgIcons\Exception\IconNotFoundException;
use Hyvor\SvgIcons\Exception\InvalidLibraryException;
use Hyvor\SvgIcons\Icon;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @phpstan-type RenderContextLanguage array{code: string, name: string}
 * @phpstan-type RenderContext array{
 *     _blog: array{
 *          subdomain: string,
 *          base_url: string,
 *          languages: RenderContextLanguage[]
 *     },
 *     _lang: RenderContextLanguage,
 *     _meta: array{url: string, title: string, featured_image?: string},
 *     _route?: array{name: string},
 *     _post?: array{
 *          published_at: int,
 *          updated_at: int,
 *          authors: array<array{name?: string, url?: string}>,
 *          variants: array<array{language: RenderContextLanguage, url: string}>,
 *          language: RenderContextLanguage,
 *          url: string
 *     },
 *     _tag?: array{
 *          language: RenderContextLanguage,
 *          url: string,
 *          variants: array<array{language: RenderContextLanguage, url: string}>
 *     },
 *     _author?: array{
 *          language: RenderContextLanguage,
 *          url: string,
 *          variants: array<array{language: RenderContextLanguage, url: string}>
 *     }
 * }
 */
class TwigExtensions extends AbstractExtension
{

    /**
     * Blogs are cached to avoid multiple DB calls
     * Indexed with subdomain in case we handle rendering for multiple blogs in one symfony boot.
     * blog subdomain => Blog
     * @var array<string, Blog>
     */
    private array $blogCache = [];

    public function __construct(
        private ThemeFilesService $themeFilesService,
        private LanguageService $languageService,
        private PermalinkService $permalinkService,
        private DataApiCaller $dataApiCaller,
        private BlogService $blogService,
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
            new TwigFilter('language_variant_url', [$this, 'languageVariantUrlFilter'], ['needs_context' => true]),
            new TwigFilter('toc', [$this, 'tocFilter'], ['is_safe' => ['html']]),
        ];
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_current_url', [$this, 'isCurrentUrlFunction'], ['needs_context' => true]),
            new TwigFunction('data', [$this, 'dataFunction'], ['needs_context' => true, 'is_variadic' => true]),
            new TwigFunction('icon', [$this, 'iconFunction'], ['is_safe' => ['html']]),
            new TwigFunction('rich_schema', [$this, 'richSchema'], ['needs_context' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param RenderContext $context
     * @throws Error
     */
    private function getBlogFromContext(array $context): Blog
    {
        $subdomain = $context['_blog']['subdomain'];

        if (isset($this->blogCache[$subdomain])) {
            return $this->blogCache[$subdomain];
        }

        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if (!$blog) {
            throw new Error("Blog not found for subdomain: $subdomain");
        }

        $this->blogCache[$subdomain] = $blog;
        return $blog;
    }

    /**
     * @param RenderContext $context
     */
    public function assetUrlFilter(array $context, string $assetName): string
    {
        $baseUrl = $context['_blog']['base_url'];
        return $baseUrl . '/assets/' . $assetName;
    }

    /**
     * @param RenderContext $context
     * @throws Error
     */
    public function assetFilter(array $context, string $assetName): string
    {
        $blog = $this->getBlogFromContext($context);
        $file = $this->themeFilesService->getFile($blog, $assetName, ThemeFileFolder::ASSETS);
        return $file?->getContent() ?? '';
    }

    /**
     * @param RenderContext $context
     * @param string[] $args
     * @throws Error
     */
    public function langFilter(array $context, string $key, array $args = []): ?string
    {
        $blog = $this->getBlogFromContext($context);
        $currentLangCode = $context['_lang']['code'];

        return $this->twigLanguage->get($blog, $currentLangCode, $key, $args);
    }

    /**
     * @param RenderContext $context
     * @param mixed[] $args
     * @throws Error
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

    /**
     * @param RenderContext $context
     */
    public function templateFilter(Environment $env, array $context, string $string): string
    {
        return $env->createTemplate($string)->render($context);
    }

    /**
     * @param RenderContext $context
     */
    public function paginationPageUrlFilter(array $context, ?int $pageNumber): string
    {
        $pageNumber ??= 1;
        $url = $context['_meta']['url'];
        $url = (string) preg_replace('/\/page\/\d+$/', '', $url);

        $url = rtrim($url, '/');
        if ($pageNumber > 1) {
            $url .= '/page/' . $pageNumber;
        }

        return $url;
    }

    /**
     * @param RenderContext $context
     * @throws Error
     */
    public function languageVariantUrlFilter(array $context, string $languageCode): string
    {
        $route = $context['_route']['name'] ?? null;

        if (
            $route === 'post' || $route === 'page' ||
            $route === 'tag' || $route === 'author'
        ) {
            $object = match ($route) {
                'post', 'page' => $context['_post'] ?? throw new Error('Post not found in context'),
                'tag' => $context['_tag'] ?? throw new Error('Tag not found in context'),
                'author' => $context['_author'] ?? throw new Error('Author not found in context'),
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

        /** @var array<array<mixed>> $languages */
        $languages = $context['_blog']['languages'];
        $language = array_find($languages, fn($lang) => $lang['code'] === $languageCode);

        if (!$language) {
            return ''; // language not found?
        }

        $languageModel = $this->languageService->getLanguageByCode(
            $this->getBlogFromContext($context),
            $language['code']
        );

        if (!$languageModel) {
            return '';
        }

        $blog = $this->getBlogFromContext($context);

        return $this->permalinkService->getBlogPermalink(
            $blog,
            $languageModel
        );
    }

    /**
     * @param RenderContext|string|null $levels
     */
    public function tocFilter(string $content, null|array|string $levels = null): string
    {
        $levels = TocHeading::getLevels($levels);
        $toc = new TocHtml($levels);
        return $toc->htmlFromHtml($content);
    }

    /** @param RenderContext $context */
    public function isCurrentUrlFunction(array $context, string $url): bool
    {
        $currentUrl = $context['_meta']['url'];
        $blogBaseUrl = $context['_blog']['base_url'];

        $currentPath = substr($currentUrl, strlen($blogBaseUrl));
        $currentPath = trim($currentPath, '/');

        // absolute URL
        if (preg_match('/^https?:\/\//', $url)) {
            // if not starting with the blog base URL, it is not the current one
            if (substr($url, 0, strlen($blogBaseUrl)) !== $blogBaseUrl) {
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

    /**
     * @param RenderContext $context
     * @param array<mixed> $params
     * @throws Error
     */
    public function dataFunction(array $context, array $params = []): mixed
    {
        $endpoint = $params['endpoint'] ?? null;
        if (!is_string($endpoint) || $endpoint === '') {
            throw new Error('endpoint is required for the data() function');
        }
        unset($params['endpoint']);

        $subdomain = $context['_blog']['subdomain'];

        /** @var array<string, mixed> $params */
        return $this->dataApiCaller->callApi($subdomain, $endpoint, $params);
    }

    /**
     * @throws Error
     */
    public function iconFunction(string $library, ?string $iconName, ?int $width = null, ?int $height = null): string
    {
        if (!$iconName) {
            throw new Error('Icon name is required for the icon() function');
        }

        try {
            $icon = new Icon($library, $iconName);
            /** @var string */
            return $icon->getSvg($width, $height);
        } catch (InvalidLibraryException) {
            throw new Error("Invalid icon library $library for $iconName");
        } catch (IconNotFoundException) {
            throw new Error("Icon not found $library - $iconName");
        }
    }

    /** @param RenderContext $context */
    public function richSchema(array $context): string
    {
        if (!isset($context['_post'])) {
            return '';
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $context['_meta']['title'],
            'datePublished' => $this->getDateTimeString($context['_post']['published_at']),
            'dateModified' => $this->getDateTimeString($context['_post']['updated_at']),
            'author' => array_map(fn($author) => array_merge(
                ['type' => '@Person'],
                !empty($author['name']) ? ['name' => $author['name']] : [],
                !empty($author['url']) ? ['url' => $author['url']] : []
            ), $context['_post']['authors'])
        ];

        if (!empty($context['_meta']['featured_image'])) {
            $schema['image'] = [$context['_meta']['featured_image']];
        }

        return '<script type="application/ld+json">' . "\n" . json_encode(
                $schema,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ) . "\n" . '</script>';
    }

    private function getDateTimeString(int $timestamp): string
    {
        return \DateTimeImmutable::createFromTimestamp($timestamp)->format(\DateTimeInterface::ATOM);
    }
}
