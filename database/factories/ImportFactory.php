<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Import;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFactory extends Factory
{
    protected $model = Import::class;

    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'name' => 'test.xml',
            'type' => 'sitemap',
            'status' => 'pending',
        ];
    }
}
