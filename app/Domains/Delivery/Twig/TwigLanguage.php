<?php
namespace App\Domains\Delivery\Twig;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\ThemeFiles\ThemeFilesRepository;
use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\BlogThemeFile;
use App\Models\Language;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Str;

class TwigLanguage {

    private array $languageStrings;

    public function __construct(Blog $blog, Language $language)
    {
        
        $fallback = LanguageRepository::getFallbackLanguage($blog, $language);

        $languageFileName = $language->code  . '.yaml';
        $fallbackFileName = $fallback->code . '.yaml';
        $defaultFileName = 'en.yaml';

        /**
         * Get the current language, fallback, and HB default
         */
        $files = ThemeFilesRepository::getMultipleFiles($blog, [
            $languageFileName,
            $fallbackFileName,
            $defaultFileName,
        ], ThemeFileFolderEnum::LANG)->keyBy('name');

        /**
         * Doing that,
         * Missing language strings will be filled with the fallback or English
         */

        // set strings to default (en)
        $this->setStrings($files[$defaultFileName]);

        // then extend with fallback
        if (isset($files[$fallbackFileName]) && $fallbackFileName !== $defaultFileName) {
            $this->extendStrings($files[$fallbackFileName]);
        }

        // finally extend with the real language
        if (isset($files[$languageFileName]) && $languageFileName !== $defaultFileName) {
            $this->extendStrings($files[$languageFileName]);
        }

    }

    private function setStrings($file)
    {
        $this->languageStrings = $this->parseYaml($file->content);
    }

    private function extendStrings($file)
    {
        $newStrings = $this->parseYaml($file->content);

        foreach ($this->languageStrings as $key => &$value) {
            if (isset($newStrings[$key])) {
                $value = $newStrings[$key];
            }
        } 
    }

    private function parseYaml(string $content)
    {
        return Yaml::parse($content);
    }

    public function get($key, $args) {

        $val = $this->languageStrings[$key] ?? '';

        if (isset($args[0])) {
            $val = str_replace('*', $args[0], $val);
        } else {

            $val = preg_replace_callback('/{(.+?)}/', function ($match) use ($args) {
                /**
                 * Twig BUG: https://github.com/twigphp/Twig/issues/3475
                 * $args are automatically changed to snake case
                 * If the argument was authorName, $args has it as author_name now
                 */
                $snakeParam = Str::snake($match[1]);
                
                return $args[$snakeParam] ?? '';
            }, $val);

        }

        return $val;

    }

}