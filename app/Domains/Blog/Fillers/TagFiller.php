<?php

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Tag\TagRepository;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\TagVariant;
use Faker\Factory;

class TagFiller implements  FillerInterface
{

    private array $data = [

        [
            'slug' => 'welcome',
            'name' => 'Welcome',
            'description' => 'Welcome to Hyvor Blogs'
        ]

    ];


    public function __construct(private Blog $blog)
    {
    }

    public function fill()
    {

        TagRepository::createTag($this->blog, 'Welcome');

        if ($this->blog->type === BlogTypeEnum::DEV) {

            $faker = Factory::create();


            foreach (range(1, 5) as $i) {
                TagRepository::createTag($this->blog, $faker->word());
            }

        }

    }
}