<?php

namespace App\Service\Import\Sitemap\PageScraper;

class PageScraperOptions
{
    public function __construct(
        public readonly string $contentSelector,
        public readonly ?string $titleSelector = null,
        public readonly ?string $descriptionSelector = null,
        public readonly ?string $contentExcludeSelector = null,
        public readonly ?string $publishedAtSelector = null,
        public readonly ?string $slugExclude = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            contentSelector: self::toStringValue($data['contentSelector'] ?? '') ?? '',
            titleSelector: self::toStringValue($data['titleSelector'] ?? null),
            descriptionSelector: self::toStringValue($data['descriptionSelector'] ?? null),
            contentExcludeSelector: self::toStringValue($data['contentExcludeSelector'] ?? null),
            publishedAtSelector: self::toStringValue($data['publishedAtSelector'] ?? null),
            slugExclude: self::toStringValue($data['slugExclude'] ?? null),
        );
    }

    private static function toStringValue(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'contentSelector' => $this->contentSelector,
            'titleSelector' => $this->titleSelector,
            'descriptionSelector' => $this->descriptionSelector,
            'contentExcludeSelector' => $this->contentExcludeSelector,
            'publishedAtSelector' => $this->publishedAtSelector,
            'slugExclude' => $this->slugExclude,
        ];
    }
}
