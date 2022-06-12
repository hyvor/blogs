<?php
namespace Database\Factories;

use App\Models\Language;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Tests\Unit\__Generators__\ProsemirrorContentGenerator;

class PostVariantFactory extends Factory
{

    public function definition()
    {

        $content = ProsemirrorContentGenerator::getParas();

        return [
            'post_id' => Post::factory(),
            'language_id' => Language::factory(),

            'status' => Arr::random(['draft', 'published', 'scheduled']),
            'content' => $content,
            'content_unsaved' => null,
            'content_html' => null,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,

            'words' => null,
        ];

    }

}
