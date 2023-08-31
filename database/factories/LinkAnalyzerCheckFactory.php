<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkAnalyzerCheck>
 */
class LinkAnalyzerCheckFactory extends Factory
{

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'status' => 'pending',
        ];
    }

}