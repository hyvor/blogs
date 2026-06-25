<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Document\Node;

class TocHeading
{
    public function __construct(
        public string $title,
        public ?string $id,
        public int $level,
    ) {
    }

    /**
     * @param int[] $levels
     * @return self[]
     */
    public static function fromHtml(string $html, array $levels): array
    {
        $levels = self::getLevels($levels);

        /** @var self[] $headings */
        $headings = [];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        if (
            !$dom->loadHTML(
                // https://stackoverflow.com/a/8218649/9059939
                '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html
            )
        ) {
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $headingsInHtml = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');

        if ($headingsInHtml === false) {
            return [];
        }

        foreach ($headingsInHtml as $heading) {
            if (!$heading instanceof \DOMElement) {
                continue;
            }
            $level = (int) substr($heading->tagName, 1);
            if (!in_array($level, $levels)) {
                continue;
            }
            $headings[] = new self(
                title: $heading->textContent,
                id: $heading->getAttribute('id') ?: null,
                level: $level,
            );
        }

        return $headings;
    }

    /**
     * @param int[] $levels
     * @return self[]
     */
    public static function fromNode(Node $node, array $levels): array
    {
        $levels = self::getLevels($levels);

        /** @var self[] $headings */
        $headings = [];

        $node->traverse(function (Node $node) use (&$headings, $levels) {
            if ($node->type->name === 'heading') {
                $level = (int) $node->attrs->get('level');
                if (!in_array($level, $levels)) {
                    return;
                }
                $id = $node->attrs->id ?? null;
                $headings[] = new self(
                    title: $node->allText(),
                    id: is_string($id) ? $id : null,
                    level: $level,
                );
            }
        });

        return $headings;
    }

    /**
     * @param null|string|array<mixed> $levels
     * @return int[]
     */
    public static function getLevels(null|string|array $levels): array
    {
        if ($levels === null) {
            return Toc::DEFAULT_LEVELS;
        }

        if (is_string($levels)) {
            $levels = explode(',', $levels);
            $levels = array_map('trim', $levels);
        }

        $levels = array_map(fn($v) => is_scalar($v) ? intval($v) : 0, $levels);

        return array_values(
            array_filter($levels, fn($level) => $level >= 1 && $level <= 6)
        );
    }
}
