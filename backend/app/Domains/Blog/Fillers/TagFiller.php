<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Tag\TagRepository;
use App\Models\Blog;
use Faker\Factory;

class TagFiller implements FillerInterface
{
//    private array $data = [
//
//        [
//            'slug' => 'welcome',
//            'name' => 'Welcome',
//            'description' => 'Welcome to Hyvor Blogs',
//        ],
//
//    ];

    public function __construct(private Blog $blog)
    {
    }

    public function fill() : void
    {
        TagRepository::createTag($this->blog, 'Welcome');

        if (
            $this->blog->type === BlogTypeEnum::DEV ||
            $this->blog->type === BlogTypeEnum::PREVIEW
        ) {
            $faker = Factory::create();

            foreach (range(1, 5) as $i) {
                TagRepository::createTag($this->blog, $faker->word());
            }
        }
    }
}
