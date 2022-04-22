<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\Request;
use ParsedownExtra;

class DocsController extends Controller
{

    public function handle(Request $request) {
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
            'nav' => include(resource_path('docs/nav.php'))
        ]);
    }

    private function getContentFromName($name) {
        $name = $name ? $name : 'index';
        $file = resource_path("docs/$name.md");

        if (file_exists($file)) {
            return file_get_contents($file);
        } else {
            return null;
        }
    }

    private function replaceDynamicData($page, $markdown) {

        if ($page === 'syntax-highlighting') {

            // replace languages
            $languages = Highlighter::getAllLanguages();

            $languageTags = '';
            foreach ($languages as $language) {
                $names = [$language->id];
                
                if (isset($language->aliases)) {
                    $names = array_merge($names, $language->aliases);
                }
                $names = implode(', ', $names);
                $languageTags .= "<span>$names</span>";
            }

            $markdown = str_replace('{{language_tags}}', $languageTags, $markdown);
            $markdown = str_replace('{{language_number}}', count($languages), $markdown);

            // themes
            $themes = Highlighter::getAllThemes();

            $code = <<<JS
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
                if ($theme === 'css-variables') continue;
                $themeTags .= "<span>$theme</span>";

                $highlighted = Highlighter::highlight(
                    $code, 
                    'jsx', 
                    $theme,
                    true, 
                    'highlight=2-3 +=10 -=11 renumber=11:10'
                );
                $previews .= "<div>
                    <div class=\"theme-key\">$theme</div>
                    $highlighted
                </div>";
            }

            $markdown = str_replace('{{theme_tags}}', $themeTags, $markdown);
            $markdown = str_replace('{{themes_number}}', count($themes), $markdown);
            $markdown = str_replace('{{theme_previews}}', $previews, $markdown);

        }

        return $markdown;

    }

}
