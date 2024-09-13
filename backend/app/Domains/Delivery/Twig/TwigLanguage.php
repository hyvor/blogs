<?php

namespace App\Domains\Delivery\Twig;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Collection;
use App\Models\ThemeFile;

class TwigLanguage
{
    /**
     * @var string[]
     */
    private array $languageStrings;

    public function __construct(Blog $blog, Language $language)
    {
        // TODO: Support fallback
        // $fallback = LanguageRepository::getFallbackLanguage($blog, $language);

        $languageFileName = $language->code.'.yaml';
        // $fallbackFileName = $fallback->code.'.yaml';
        $defaultFileName = 'en.yaml';

        /**
         * Get the current language, fallback, and HB default
         */
        $files = ThemeFilesRepository::getMultipleFiles($blog, [
            $languageFileName,
            // $fallbackFileName,
            $defaultFileName,
        ], ThemeFileFolderEnum::LANG)->keyBy('name');

        /**
         * Doing that,
         * Missing language strings will be filled with the fallback or English
         */

        if ($files[$defaultFileName]) {
            // set strings to default (en)
            $this->setStrings($files[$defaultFileName]);
        }

        // then extend with fallback
//        if (isset($files[$fallbackFileName]) && $fallbackFileName !== $defaultFileName) {
//            $this->extendStrings($files[$fallbackFileName]);
//        }

        // finally extend with the real language
        if (isset($files[$languageFileName]) && $languageFileName !== $defaultFileName) {
            $this->extendStrings($files[$languageFileName]);
        }
    }

    private function setStrings(ThemeFile $file) : void
    {
        if ($file->content) {
            $this->languageStrings = $this->parseYaml($file->content);            
        }
    }

    private function extendStrings(ThemeFile $file) : void
    {
        if ($file->content) {
            $newStrings = $this->parseYaml($file->content);

            foreach ($this->languageStrings as $key => &$value) {
                if (isset($newStrings[$key])) {
                    $value = $newStrings[$key];
                }
            }   
        }
    }

    /**
     * @return string[]
     */
    private function parseYaml(string $content) : array
    {
        return Yaml::parse($content);
    }

    /**
     * @param string[] $args
     */
    public function get(string $key, array $args) : ?string
    {
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
