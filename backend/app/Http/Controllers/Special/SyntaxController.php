<?php declare(strict_types=1);

namespace App\Http\Controllers\Special;

use App\Domains\Delivery\Twig\TwigRenderer;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SyntaxController
{

    public function getData() : JsonResponse
    {

        if (Cache::has('docs_syntax_highlighting')) {
            return response()->json(Cache::get('docs_syntax_highlighting'));
        } else {

            // replace languages
            $languages = Highlighter::getAllLanguages();
            $languagesCount = count($languages);

            $languageTags = '';
            foreach ($languages as $language) {
                $names = [$language->id];

                if (isset($language->aliases)) {
                    $names = array_merge($names, $language->aliases);
                }
                $names = implode(', ', $names);
                $languageTags .= "<span>$names</span>";
            }

            // themes
            $themes = Highlighter::getAllThemes();
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
                $themeTags .= "<span>$theme</span>";

                $highlightData = Highlighter::highlight(
                    $code,
                    'jsx',
                    $theme,
                    true,
                    'h=2-3 +=10 -=11 renumber=11:10'
                );
                $highlightData['pre']['class'] = str_replace(
                    'language-jsx',
                    '',
                    $highlightData['pre']['class']
                );
                $highlighted = TwigRenderer::renderFile(resource_path('twig/blocks/code.twig'), [
                    'data' => $highlightData
                ]);

                $previews .= "<div>
                        <div class=\"theme-key\">$theme</div>
                        $highlighted
                    </div>";
            }

            $data = [
                'languageTags' => $languageTags,
                'languagesCount' => $languagesCount,
                'themeTags' => $themeTags,
                'themesCount' => $themesCount,
                'previews' => $previews,
            ];

            Cache::put('docs_syntax_highlighting', $data);

            return response()->json($data);
        }
    }

}