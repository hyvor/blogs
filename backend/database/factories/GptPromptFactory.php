<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\GptPrompt;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GptPrompt>
 */
class GptPromptFactory extends Factory
{

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'post_id' => Post::factory(),
            'prompt' => $this->faker->text(100),
            'gpt_response' => $this->faker->text(100),
            'model_name' => 'gpt-3',
            'tokens_prompt' => rand(1,100),
            'tokens_response' => rand(1,100),
            'tokens_total' => rand(1,100),
        ];
    }

}