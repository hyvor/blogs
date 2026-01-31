<?php

namespace App\Domains\Delivery\Twig;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Content\Nodes\Toc\Toc;
use App\Domains\Post\Content\Nodes\Toc\TocHeading;
use App\Domains\Post\Content\Nodes\Toc\TocHtml;
use App\Domains\Route\PermalinkRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Carbon\Carbon;
use Hyvor\SvgIcons\Exception\IconNotFoundException;
use Hyvor\SvgIcons\Exception\InvalidLibraryException;
use Hyvor\SvgIcons\Exception\SvgIconException;
use Hyvor\SvgIcons\Icon;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use DateTime;

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
    public Blog $blog;
    public TwigLanguage $twigLanguageHandler;

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
            new TwigFilter('toc', [$this, 'tocFilter'], ['is_safe' => ['html']]),
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
            new TwigFunction('rich_schema', [$this, 'richSchema'], [
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param array<mixed> $context
     */
    public function assetUrlFilter(array $context, string $assetName) : string
    {

        /**
         * Here, we cannot use PermalinkRepository
         * because it requires Blog $blog, which has a wrong base URL
         * when taken through ->getBlogFromContext for LocalDev requests
         * So, we simple use the BlogObject
         */

        return $context['_blog']['base_url'] . '/assets/' . $assetName;
    }

    /**
     * @param string[] $context
     */
    public function assetFilter(array $context, string $assetName) : string
    {
        $blog = $this->getBlogFromContext($context);
        $file = ThemeFilesRepository::getFile($blog, $assetName, ThemeFileFolderEnum::ASSETS);

        return $file?->content ?? '';
    }

    /**
     * @param mixed[] $context
     * @param string[] $args
     */
    public function langFilter(array $context, string $key, array $args = []) : ?string
    {
        $blog = $this->getBlogFromContext($context);
        $currentLanguage = LanguageRepository::getLanguageByCode($blog, $context['_lang']['code']);

        if (! isset($this->twigLanguageHandler) && $currentLanguage) {
            $this->twigLanguageHandler = new TwigLanguage($blog, $currentLanguage);
        }

        return $this->twigLanguageHandler->get($key, $args);
    }

    /**
     * @param mixed[] $context
     * @param string[] $args
     */
    public function langByNumberFilter(array $context, string $value, array $args = []) : ?string
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

        return $this->langFilter($context, (string) $key, [(string) $value]);
    }

    /**
     * @param mixed[] $context
     */
    public function templateFilter(Environment $env, array $context, string $string) : string
    {
        $template = $env->createTemplate($string);
        $html = $template->render($context);

        return $html;
    }

    /**
     * @param mixed[] $context
     */
    public function paginationPageUrlFilter(array $context, ?int $pageNumber) : string
    {
        $pageNumber ??= 1;

        $url = $context['_meta']['url'];
        $url = preg_replace('/\/page\/\d+$/', '', $url);

        $url = rtrim($url, '/');
        if ($pageNumber > 1) {
            $url .= '/page/'.$pageNumber;
        }

        return $url;
    }

    public function languageVariantUrlFilter(mixed $context, string $languageCode): string
    {
        $route = $context['_route']['name'] ?? null;

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

        /** @var array<array<mixed>> $languages */
        $languages = $context['_blog']['languages'];
        $language = collect($languages)->firstWhere('code', $languageCode);

        if (!$language) {
            return ''; // language not found?
        }

        $languageModel = LanguageRepository::getLanguageByCode(
            $this->getBlogFromContext($context),
            $language['code']
        );

        if (!$languageModel) {
            return '';
        }

        $blog = $this->getBlogFromContext($context);

        return PermalinkRepository::getBlogPermalink(
            $blog,
            $languageModel
        );
    }

    /**
     * @param string[] $context
     * @param string[] $params
     */
    public function dataFunction(array $context, array $params = []) : mixed
    {
        $blog = $this->getBlogFromContext($context);

        $endpoint = $params['endpoint'] ?? null;

        if (! $endpoint) {
            throw new Error('endpoint is required for the data() function');
        }

        unset($params['endpoint']);

        try {
            $response = app(DataAPICaller::class)->callApi($blog->subdomain, $endpoint, $params);
        } catch (TrustedException $e) {
            // throw twig error
            throw new Error("Error when calling the Data API  /$endpoint endpoint: ".$e->getMessage());
        }

        return $response;
    }

    public function iconFunction(string $library, ?string $iconName, ?int $width = null, ?int $height = null): string
    {

        if (!$iconName) {
            throw new Error('Icon name is required for the icon() function');
        }

        try {
            $icon = new Icon($library, $iconName);

            return $icon->getSvg($width, $height);
        } catch (InvalidLibraryException $e) {
            throw new Error("Invalid icon library $library for $iconName");
        } catch (IconNotFoundException) {
            throw new Error("Icon not found $library - $iconName");
        }
    }

    /**
     * checks if a given URL is the current one
     * @param mixed[] $context
     */
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
     * @param array<mixed>|string|null $levels
     */
    public function tocFilter(string $content, null|array|string $levels = null): string
    {
        $levels = TocHeading::getLevels($levels);
        $toc = new TocHtml($levels);
        return $toc->htmlFromHtml($content);
    }

    /**
     * @param array<mixed> $context
     */
    public function richSchema(array $context): string
    {
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

        return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . '</script>';
    }

    /**
     * @param array<mixed> $context
     */
    private function getBlogFromContext($context): Blog
    {
        if (! isset($this->blog)) {
            $subdomain = $context['_blog']['subdomain'];
            $blog = BlogService::getBlogBySubdomain($subdomain);
            if (!$blog) {
                throw new Error('Blog not found');
            }
            $this->blog = $blog;
        }

        return $this->blog;
    }

    private function getDateTimeString(string $timestamp): string
    {
        return Carbon::createFromTimestamp($timestamp)->toIso8601String();
    }
}
