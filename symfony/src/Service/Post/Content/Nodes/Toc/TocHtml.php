<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Document\Node;

/**
 * @phpstan-type TocEntry array{title: string, id: string|null, level: int, children: mixed[]}
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

    public function htmlFromNode(Node $node): string
    {
        return $this->headingsToHtml(TocHeading::fromNode($node, $this->levels));
    }

    /**
     * @return TocEntry[]
     */
    public function arrayFromNode(Node $node): array
    {
        $headings = TocHeading::fromNode($node, $this->levels);
        return $this->buildToc($headings);
    }

    /**
     * @param TocHeading[] $headings
     */
    private function headingsToHtml(array $headings): string
    {
        $levelsString = implode(',', $this->levels);

        $html = "<div class=\"toc\" data-levels=\"$levelsString\">";
        $html .= $this->childrenToHtml($this->buildToc($headings));
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
            /** @var TocEntry[] $entryChildren */
            $entryChildren = $entry['children'];
            if (!empty($entryChildren)) {
                $ul .= $this->childrenToHtml($entryChildren);
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
