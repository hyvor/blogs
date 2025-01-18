<?php

function getAllSvelteFiles($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) == "svelte") {
                $results[] = $path;
            }
        } else if ($value != "." && $value != "..") {
            getAllSvelteFiles($path, $results);
        }
    }

    return $results;
}

$files = getAllSvelteFiles("frontend/src");

$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);

    $regex = "/import\s*{([\sa-z0-9A-Z,]+)}\s*from\s+['\"]@hyvor\/icons['\"];?/";
    $regex = trim($regex);

    if (preg_match($regex, $content, $matches)) {
        $full = $matches[0];
        $imports = explode(",", $matches[1]);

        $newImports = "";

        foreach ($imports as $import) {
            $import = trim($import);
            $newImports .= "import $import from '@hyvor/icons/$import';\n";
        }

        echo $matches[0] . "\n---\n" . $newImports . "\n\n";

        $content = str_replace($full, $newImports, $content);
        file_put_contents($file, $content);

        $count++;
    }


}

echo $count . "\n";