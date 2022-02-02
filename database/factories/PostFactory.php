<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $title = $this->faker->sentence;
        $status = Arr::random(['draft', 'published', 'deleted', 'scheduled']);

        $paragraphs = $this->faker->paragraphs(rand(2, 6));
        $prosemirrorJson = [
            'type' => 'doc',
            'content' => []
        ];
        foreach ($paragraphs as $para) {
            $prosemirrorJson['content'][] = [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => $para
                ]]
            ];
        }

        return [
            'content' => json_encode($prosemirrorJson),
            'title' => $title,
            'slug' => Str::slug($title),
            'published_at' => $status === 'published' ? now() : null,
            'description' => $this->faker->sentence,
            'status' => $status,
        ];
    }
}
