<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HyvorTalkGatedContentRule>
 */
class HyvorTalkGatedContentRuleFactory extends Factory
{

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'tag_id' => rand(),
            'minimum_plan' => 'Premium',
            'gate' => null
        ];
    }

}