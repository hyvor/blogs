<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Toc;

use App\Domains\Post\Content\Nodes\Heading\Heading;
use Hyvor\Phrosemirror\Document\Node;

/**
 * @phpstan-type TocEntry {title: string, id: string | null, level: int, children: TocEntry[]}
 */
class TocHtml
{

    public function __construct(
        /** int[] */
        private array $levels,
    ) {}

    public function htmlFromHtml(string $html) : string
    {
        return $this->headingsToHtml(TocHeading::fromHtml($html, $this->levels));
    }

    public function htmlFromNode(Node $node) : string
    {
        return $this->headingsToHtml(TocHeading::fromNode($node, $this->levels));
    }

    /**
     * @return TocEntry[]
     */
    public function arrayFromNode(Node $node) : array
    {
        $headings = TocHeading::fromNode($node, $this->levels);
        return $this->buildToc($headings);
    }

    private function headingsToHtml(array $headings) : string
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
     * @param TocHeading[] $headings
     * @return TocEntry[]
     */
    private function buildToc(array $headings, ?int $previousLevel = null) : array
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