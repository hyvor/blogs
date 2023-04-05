<?php declare(strict_types=1);

namespace App\Http\Controllers\Pages;

use App\Domains\Delivery\Twig\TwigRenderer;
use App\Http\Controllers\Controller;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ParsedownExtra;
use View;

class DocsController extends Controller
{
    public function handle(Request $request) : View
    {
        $page = $request->route('page') ?? 'index';
        $content = $this->getContentFromName($page);

        if (is_null($content)) {
            return abort(404);
        }

        $parseDown = new ParsedownExtra();
        $content = $parseDown->text($content);

        $content = $this->replaceDynamicData($page, $content);

        preg_match('/<h1>(.+)<\/h1>/', $content, $matches);
        $title = $matches[1] ?? 'Hyvor Blogs Docs';

        return view('landing.docs', [
            'pageName' => $page,
            'content' => $content,
            'title' => $title,
            'nav' => include(resource_path('docs/nav.php')),
        ]);
    }

    private function getContentFromName($name) : ?string
    {
        $name = $name ? $name : 'index';
        $file = resource_path("docs/$name.md");

        if (file_exists($file)) {
            return file_get_contents($file);
        } else {
            return null;
        }
    }

    private function replaceDynamicData($page, $markdown)
    {
        if ($page === 'syntax-highlighting') {

            if (Cache::get('docs_syntax_highlighting')) {
                [
                    'languageTags' => $languageTags,
                    'languagesCount' => $languagesCount,
                    'themeTags' => $themeTags,
                    'themesCount' => $themesCount,
                    'previews' => $previews,
                ] = Cache::get('docs_syntax_highlighting');
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

                Cache::put('docs_syntax_highlighting', [
                    'languageTags' => $languageTags,
                    'languagesCount' => $languagesCount,
                    'themeTags' => $themeTags,
                    'themesCount' => $themesCount,
                    'previews' => $previews,
                ]);

            }

            $markdown = str_replace('{{language_tags}}', $languageTags, $markdown);
            $markdown = str_replace('{{language_number}}', $languagesCount, $markdown);
            $markdown = str_replace('{{theme_tags}}', $themeTags, $markdown);
            $markdown = str_replace('{{themes_number}}', $themesCount, $markdown);
            $markdown = str_replace('{{theme_previews}}', $previews, $markdown);
        }

        return $markdown;
    }
}
