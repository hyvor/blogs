<?php declare(strict_types=1);

namespace App\Domains\Integrations\DeepL;

use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Domains\Integrations\DeepL\Exceptions\DeepLApiException;
use App\Domains\Integrations\DeepL\Exceptions\DeepLHtmlProcessingException;
use App\Domains\Post\Content\PostContentOptions;
use App\Domains\Post\Content\PostContentService;
use App\Models\Blog;

class DeepLPostTranslator
{

    /**
     * @var array<int, string>
     */
    private array $codeBlocks = [];

    public function __construct(
        private Blog $blog,
        private string $content,
        private string $title,
        private string $description,
        private string $slug,
        private DeepLSourceLangEnum $sourceLang,
        private DeepLTargetLangEnum $targetLang
    ) {}

    /**
     * @return array{title: string, description: string, content: string, slug: string, chars: int}
     * @throws DeepLHtmlProcessingException
     * @throws DeepLApiException
     */
    public function translate() : array
    {

        $html = PostContentService::getHtml(
            $this->content,
            $this->blog,
            new PostContentOptions(
                isCodeBlockPlain: true
            )
        );

        /**
         * DeepL doesn't preserve whitespaces in code blocks
         * so we replace them with a placeholder and then replace them back
         */
        $html = preg_replace_callback('/<pre(.*?)><code>(.*?)<\/code><\/pre>/s', function($matches) {
            $index = count($this->codeBlocks);
            $this->codeBlocks[$index] = $matches[2];
            $attributes = $matches[1];
            return '<pre' . $attributes . '><code>' . $index . '</code></pre>';
        }, $html);

        if (!$html) {
            throw new DeepLHtmlProcessingException('Unable to replace code blocks'); // @codeCoverageIgnore
        }

        $chars = strlen(strip_tags($html)) + strlen($this->title) + strlen($this->description);

        [
            $translatedTitle,
            $translatedDescription,
            $translatedSlug,
            $translatedHtml,
        ] = DeepLService::translate([
            $this->title,
            $this->description,
            $this->slug,
            $html
        ], $this->sourceLang, $this->targetLang);

        // replace code blocks
        $translatedHtml = preg_replace_callback('/<pre(.*?)><code>(.*?)<\/code><\/pre>/s', function($matches) {
            return '<pre' . $matches[1] . '><code>' . $this->codeBlocks[intval($matches[2])] . '</code></pre>';
        }, $translatedHtml);

        if (!$translatedHtml) {
            throw new DeepLHtmlProcessingException('Unable to replace code blocks back'); // @codeCoverageIgnore
        }

        $content = PostContentService::getJsonFromHtml($translatedHtml, $this->blog);

        return [
            'title' => $translatedTitle,
            'description' => $translatedDescription,
            'slug' => $translatedSlug,
            'content' => $content,
            'chars' => $chars,
        ];

    }

}