<?php
namespace App\Domains\BlogTheme\Twig;

use Twig\Environment;
use Twig\Loader\ArrayLoader;

class Renderer {

    public static function renderFile(string $file, array $vars) {
        return self::renderString(file_get_contents($file), $vars);
    }

    public static function renderString(string $string, array $vars) {
        $fakeFileName = 'index.twig';

        $loader = new ArrayLoader([
            $fakeFileName => $string
        ]);

        $twig = new Environment($loader, [
            'cache' => false
        ]);

        return $twig->render($fakeFileName, $vars);
    }

    public static function renderFromFiles(array $files, array $vars, string $fileName) {

        $loader = new ArrayLoader($files);

        $twig = new Environment($loader, [
            'cache' => false
        ]);

        return $twig->render($fileName, $vars);

    }

}
