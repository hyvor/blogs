<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Toc;

use Hyvor\Phrosemirror\Document\Node;

/**
 * @phpstan-type TocHeading {title: string, id: string | null, level: int}
 * @phpstan-type TocEntry {title: string, id: string | null, level: int, children: TocEntry[]}
 */
class TocHtml
{

    public function __construct(
        private Node $doc,
        /** int[] */
        private array $levels,
    ) {}

    /**
     * @return TocEntry[]
     */
    public function toArray() : array
    {
        $headings = $this->getHeadings();
        return $this->buildToc($headings);
    }

    public function toHtml() : string
    {

        $levelsString = implode(',', $this->levels);

        $html = "<div class=\"toc\" data-levels=\"$levelsString\">";

        $array = $this->toArray();
        $childrenHtml = $this->childrenToHtml($array);

        $html .= $childrenHtml;
        $html .= '</div>';

        return $html;

    }

    /**
     * @param TocEntry[] $children
     */
    private function childrenToHtml(array $children) : string
    {

        $ul = '<ul>';

        foreach ($children as $entry) {
            $ul .= '<li>';
            $ul .= '<a href="#' . ($entry['id'] ?? '') . '">' . $entry['title'] . '</a>';
            if (!empty($entry['children'])) {
                $ul .= $this->childrenToHtml($entry['children']);
            }
            $ul .= '</li>';
        }

        $ul .= '</ul>';

        return $ul;
    }

    /**
     * @return TocHeading[]
     */
    private function getHeadings() : array
    {

        $headings = [];

        $this->doc->traverse(function (Node $node) use (&$headings) {
            if ($node->type->name === 'heading') {
                $level = $node->attrs->level;
                if (!in_array($level, $this->levels)) {
                    return;
                }
                $headings[] = [
                    'title' => $node->allText(),
                    'id' => $node->attrs->id ?? null,
                    'level' => $node->attrs->level,
                ];
            }
        });

        return $headings;

    }

    /**
     * @param TocHeading[] $headings
     * @return TocEntry[]
     */
    private function buildToc(array $headings, ?int $previousLevel = null) : array
    {

        $toc = [];

        /** @var ?int $currentLevel */
        $currentLevel = null;

        foreach ($headings as $index => $heading) {

            if ($previousLevel !== null && $heading['level'] <= $previousLevel) {
                break;
            }

            if ($currentLevel && $heading['level'] > $currentLevel) {
                continue;
            }

            $toc[] = [
                'title' => $heading['title'],
                'id' => $heading['id'],
                'level' => $heading['level'],
                'children' => $this->buildToc(
                    array_slice($headings, $index + 1),
                    $heading['level']
                ),
            ];

            $currentLevel = $heading['level'];

        }

        return $toc;

    }

}