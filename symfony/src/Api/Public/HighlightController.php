<?php

namespace App\Api\Public;

use App\Service\CodeHighlight\Highlighter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class HighlightController extends AbstractController
{
    public function __construct(
        private Highlighter $highlighter,
        private CacheInterface $cache,
    ) {}

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/highlighting-docs', name: 'highlighting_docs', methods: ['GET'])]
    public function highlightingDocs(): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $this->cache->get('highlighting_docs', function (ItemInterface $item) {
            $item->expiresAfter(null);

            $languages = $this->highlighter->getAllLanguages();
            $languagesCount = count($languages);

            $languageTags = '';
            foreach ($languages as $language) {
                $languageTags .= '<span>' . htmlspecialchars($language) . '</span>';
            }

            $themes = $this->highlighter->getAllThemes();
            $themesCount = count($themes);

            $code = <<<'JS'
            function App() {
                const [clicks, setClicks] = useState(0);

                function handleClick() {
                    setClicks(clicks + 1);
                }

                return <div onClick={handleClick}>
                    { /* Print clicks */ }
                    Clicks: {  }
                    Clicks: { clicks }
                </div>
            }
            JS;

            $themeTags = '';
            $previews = '';

            foreach ($themes as $theme) {
                $themeTags .= '<span>' . htmlspecialchars($theme) . '</span>';

                $highlightData = $this->highlighter->highlight($code, 'jsx', $theme, true, 'h=2-3 +=10 -=11 renumber=11:10');
                /** @var array{style: string, class: string, onmouseenter: string, onmouseleave: string} $pre */
                $pre = $highlightData['pre'];
                /** @var string $highlightedCode */
                $highlightedCode = $highlightData['code'];
                $preClass = str_replace('language-jsx', '', $pre['class']);

                $previews .= '<div>'
                    . '<div class="theme-key">' . htmlspecialchars($theme) . '</div>'
                    . $this->renderCodeBlock($pre['style'], $pre['onmouseenter'], $pre['onmouseleave'], $preClass, $highlightedCode)
                    . '</div>';
            }

            return [
                'languageTags' => $languageTags,
                'languagesCount' => $languagesCount,
                'themeTags' => $themeTags,
                'themesCount' => $themesCount,
                'previews' => $previews,
            ];
        });

        return $this->json($data);
    }

    private function renderCodeBlock(
        string $style,
        string $onmouseenter,
        string $onmouseleave,
        string $class,
        string $code,
    ): string {
        $style = htmlspecialchars($style);
        $onmouseenter = htmlspecialchars($onmouseenter);
        $onmouseleave = htmlspecialchars($onmouseleave);
        $class = htmlspecialchars($class);

        return "<pre style=\"$style\" onmouseenter=\"$onmouseenter\" onmouseleave=\"$onmouseleave\" class=\"$class\"><code>$code</code></pre>";
    }
}
