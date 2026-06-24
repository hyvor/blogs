<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Embed;

use App\Service\Post\Content\UrlDataService;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;
use Hyvor\Unfold\Exception\UnfoldException;

class Embed extends NodeType
{
    public string $name = 'embed';
    public string $attrs = EmbedAttrs::class;

    public function __construct(private UrlDataService $urlDataService)
    {
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'x-embed',
                getAttrs: function ($el) {
                    $url = $el->getAttribute('data-url');

                    if (!$url) {
                        return false;
                    }

                    return EmbedAttrs::fromArray([
                        'url' => $url,
                    ]);
                }
            ),
        ];
    }

    public function toHtml(Node $node, string $children): string
    {
        /** @var string $url */
        $url = $node->attr('url') ?? '';
        $embedContent = null;

        if ($url) {
            try {
                $data = $this->urlDataService->getEmbed($url);
                $embedContent = is_string($data['embed'] ?? null) ? $data['embed'] : null;
            } catch (\Exception $e) {
                $errorMessage = $e instanceof UnfoldException ? $e->getMessage() : 'unknown error';
                $embedContent = '<!-- Unable to fetch embed for URL: ' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '. Error: ' . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . ' -->';
            }
        }

        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return $embedContent !== null ? '<x-embed data-url="' . $safeUrl . '">' . $embedContent . '</x-embed>' : '';
    }
}
