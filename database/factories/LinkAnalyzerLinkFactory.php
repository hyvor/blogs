<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blog;
use App\Models\LinkAnalyzerLink;
use App\Models\PostVariant;
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
            'post_variant_id' => PostVariant::factory(),
            'blog_id' => Blog::factory(),
            'url' => $this->faker->url,
            'status_code' => $this->faker->numberBetween(200, 500),
            'ignore' => false
        ];
    }
}