<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\Helper\Generator\PostContentGenerator;

/**
 * @extends Factory<PostVariant>
 */
class PostVariantFactory extends Factory
{

    protected $model = PostVariant::class;

    public function definition()
    {
        $content = PostContentGenerator::generateRandom();

        return [
            'post_id' => Post::factory(),
            'language_id' => Language::factory(),

            'slug' => Str::slug($this->faker->text),

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
