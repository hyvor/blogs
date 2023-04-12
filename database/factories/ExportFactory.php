<?php

namespace Database\Factories;

use App\Data\Enums\ExportFormatEnum;
use App\Data\Enums\JobStatusEnum;
use App\Models\Blog;
use App\Models\Export;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Export>
 */
class ExportFactory extends Factory
{

    /**
     * @return array<mixed>
     */
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'format' => ExportFormatEnum::HYVOR_BLOGS,
            'status' => JobStatusEnum::PENDING,
            'url' => null,
            'error' => null,
        ];
    }

}