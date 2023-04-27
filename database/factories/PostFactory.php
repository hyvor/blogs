<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<mixed>
     */
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),

            'is_page' => false,
            'is_featured' => false,

            // 'slug' => Str::slug($this->faker->text),
            'published_at' => $this->faker->dateTime(),
        ];
    }
}
