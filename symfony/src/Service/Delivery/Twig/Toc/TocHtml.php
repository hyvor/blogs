<?php

namespace App\Service\Delivery\Twig\Toc;

/**
 * @phpstan-type TocEntry array{title: string, id: string|null, level: int, children: mixed[]}
 * TODO: migrate to post content services when post endpoints are migrated
 */
class TocHtml
{
    public function __construct(
        /** @var int[] */
        private array $levels,
    ) {}

    public function htmlFromHtml(string $html): string
    {
        return $this->headingsToHtml(TocHeading::fromHtml($html, $this->levels));
    }

    /**
     * @param TocHeading[] $headings
     */
    private function headingsToHtml(array $headings): string
    {
        $levelsString = implode(',', $this->levels);

        $html = "<div class=\"toc\" data-levels=\"$levelsString\">";

        $array = $this->buildToc($headings);
        $childrenHtml = $this->childrenToHtml($array);

        $html .= $childrenHtml;
        $html .= '</div>';

        return $html;
    }

    /**
     * @param TocEntry[] $children
     */
    private function childrenToHtml(array $children): string
    {
        $ul = '<ul>';

        foreach ($children as $entry) {
            $ul .= '<li>';
            $ul .= '<a href="#' . ($entry['id'] ?? '') . '">' . $entry['title'] . '</a>';
            if (!empty($entry['children'])) {
                /** @phpstan-ignore argument.type (TocEntry is self-referential; PHPStan doesn't support recursive type aliases here) */
                $ul .= $this->childrenToHtml($entry['children']);
            }
            $ul .= '</li>';
        }

        $ul .= '</ul>';

        return $ul;
    }

    /**
     * @param TocHeading[] $headings
     * @return TocEntry[]
     */
    private function buildToc(array $headings, ?int $previousLevel = null): array
    {
        $toc = [];

        /** @var ?int $currentLevel */
        $currentLevel = null;

        foreach ($headings as $index => $heading) {
            if ($previousLevel !== null && $heading->level <= $previousLevel) {
                break;
            }

            if ($currentLevel && $heading->level > $currentLevel) {
                continue;
            }

            $toc[] = [
                'title' => $heading->title,
                'id' => $heading->id,
                'level' => $heading->level,
                'children' => $this->buildToc(
                    array_slice($headings, $index + 1),
                    $heading->level
                ),
            ];

            $currentLevel = $heading->level;
        }

        return $toc;
    }
}
