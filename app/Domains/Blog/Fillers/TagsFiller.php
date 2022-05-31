<?php

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Tag\TagRepository;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\TagVariant;

class TagsFiller implements  FillerInterface
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

        foreach ($this->data as $row) {
            TagRepository::createTag($this->blog, $row['slug'], $row['name'], $row['description']);
        }

        if ($this->blog->type === BlogTypeEnum::DEV) {

            $tags = Tag::factory()->count(10)->create(['blog_id' => $this->blog]);

            $languages = $this->blog->languages;

            foreach ($languages as $language) {

                foreach ($tags as $tag) {

                    TagVariant::factory()->create([
                        'tag_id' => $tag,
                        'language_id' => $language
                    ]);

                }

            }

        }

    }
}