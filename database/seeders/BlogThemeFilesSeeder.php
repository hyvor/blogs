<?php

namespace Database\Seeders;

use App\Models\ThemeFile;
use Illuminate\Database\Seeder;

class BlogThemeFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($blogId = null)
    {

        /**
         * Insert all files in tests/data/default-theme
         * to the database
         */
        $folders = ['', 'assets', 'lang', 'styles', 'templates'];

        foreach ($folders as $folder) {

            //https://stackoverflow.com/a/15774702/9059939
            $folderPath = base_path('tests/data/default-theme/').$folder;

            $files = scandir($folderPath);
            $files = array_diff($files, ['.', '..']);

            foreach ($files as $file) {
                $filePath = "$folderPath/$file";

                if (is_dir($filePath)) {
                    continue;
                }

                $content = file_get_contents($filePath);

                ThemeFile::create([
                    'blog_id' => $blogId ?? 1,
                    'name' => $file,
                    'content' => $content,
                    'folder' => $folder === '' ? null : $folder,
                ]);
            }
        }
    }
}
