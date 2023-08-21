<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkAnalyzerLink>
 */
class LinkAnalyzerLinkFactory extends Factory
{

    protected $model = LinkAnalyzerLink::class;

    public function definition()
    {
        return [
            'last_checked_at' => now(),
            'blog_id' => Blog::factory(),
            'url' => $this->faker->url,
            'status_code' => $this->faker->numberBetween(200, 500),
            'ignore' => false
        ];
    }
}