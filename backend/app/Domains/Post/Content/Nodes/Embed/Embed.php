<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Embed;

use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Domains\UrlData\UrlDataRepository;
use Exception;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Embed extends NodeType
{

    public string $name = 'embed';
    public string $attrs = EmbedAttrs::class;

    public function fromHtml(): array
    {

        return [
            new ParserRule(
                tag: 'x-embed',
                getAttrs: function ($el) {
                    $url = $el->getAttribute('data-url');

                    if (!$url)
                        return false;

                    return EmbedAttrs::fromArray([
                        'url' => $url,
                    ]);
                }
            )
        ];

    }

    public function toHtml(Node $node, string $children): string
    {

        $embedContent = null;
        $url = strval($node->attr('url'));

        try {

            if ($url) {
                /**
                 * Usually, the embed URL is already resolved and saved in the database
                 * at the time the user embeds it in the editor.
                 * So, we don't have to worry about the applciation making a HTTP call
                 * It is a simple database call
                 */
                $urlData = app(UrlDataRepository::class)->fetch($url, UrlDataFetchTypeEnum::EMBED);
                $embedContent = $urlData['embed'];
            }

        } catch (Exception) {
        }

        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return $embedContent ? "<x-embed data-url=\"$safeUrl\">" . $embedContent . '</x-embed>' : '';

    }

}