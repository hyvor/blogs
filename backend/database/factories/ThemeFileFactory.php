<?php

namespace Database\Factories;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Models\Blog;
use App\Models\ThemeFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThemeFile>
 */
class ThemeFileFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'folder' => null,
            'name' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'content' => $this->faker->word(),
        ];
    }

    public static function templateFor(Blog $blog, string $content, string $name = 'index.twig'): ThemeFile
    {
        return ThemeFile::factory()->create([
            'blog_id' => $blog->id,
            'folder' => ThemeFileFolderEnum::TEMPLATES,
            'name' => $name,
            'content' => $content,
        ]);
    }
}
