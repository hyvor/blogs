<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Blog;
use App\Models\Import;

class ImportFactory extends Factory
{
    protected $model = Import::class;
    
    public function definition()
    {
        return [
            'blog_id' => '1',
            'name' => 'C3Ci8A59yWteysDryR9PmzIYb9YqM64LOkBC1P4Y.xml',
            'type' => 'wordpress',
            'status' => 'success',
        ];
    }
}
