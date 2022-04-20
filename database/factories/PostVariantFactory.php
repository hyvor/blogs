<?php
namespace Database\Factories;

use App\Models\Language;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class PostVariantFactory extends Factory
{

    public function definition()
    {

        $paragraphs = $this->faker->paragraphs(rand(2, 6));
        $content = [
            'type' => 'doc',
            'content' => []
        ];
        foreach ($paragraphs as $para) {
            $content['content'][] = [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => $para
                ]]
            ];
        }
        $content = json_encode($content);

        return [
            'post_id' => Post::factory(),
            'language_id' => Language::factory(),

            'status' => Arr::random(['draft', 'published', 'scheduled']),
            'content' => $content,
            'title' => $this->faker->sentence,
            'description' => $this->faker->sentence,
        ];

    }

}