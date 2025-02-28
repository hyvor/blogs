<?php

namespace Tests\Feature\DataApi;

use Database\Factories\BlogFactory;
use Database\Factories\PostFactory;
use Tests\Case\DatabaseTestCase;

class PostSearchTest extends DatabaseTestCase
{

    public function testSearchesPostsInEnglish(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $post = PostFactory::oneFor($blog, [], [
            'title' => 'How to make a cake',
            'status' => 'published',
            'ts_language' => 'english'
        ]);

        $post = PostFactory::oneFor($blog, [], [
            'title' => 'How to make a pie',
            'status' => 'published',
            'ts_language' => 'english'
        ]);

        // page, not included
        $post = PostFactory::oneFor(
            $blog,
            [
                'is_page' => true
            ],
            [
                'title' => 'How to make a pie',
                'status' => 'published',
                'ts_language' => 'english',
            ]
        );

        //  cake
        $this->dataApi($blog, '/posts/search', [
            'search' => 'cake',
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'How to make a cake');

        // stemming
        $this->dataApi($blog, '/posts/search', [
            'search' => 'making',
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'How to make a cake')
            ->assertJsonPath('data.1.title', 'How to make a pie');
    }

    public function testSearchesInFrenchAlsoSearchesDescriptionAndContent(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $post1 = PostFactory::oneFor($blog, [], [
            'description' => 'Comment faire un gâteau',
            'status' => 'published',
            'ts_language' => 'french'
        ]);

        $post2 = PostFactory::oneFor($blog, [], [
            'content_text' => 'Étape 1: mélanger les ingrédients',
            'status' => 'published',
            'ts_language' => 'french'
        ]);

        $this->dataApi($blog, '/posts/search', [
            'search' => 'étape',
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post2->id);
    }

    public function testSearchesWithPartialWords(): void
    {
        $blog = BlogFactory::withLanguageAndRoutes();

        $post = PostFactory::oneFor($blog, [], [
            'title' => 'Wordpress Alternatives',
            'status' => 'published',
            'ts_language' => 'english'
        ]);

        PostFactory::oneFor($blog, [], [
            'title' => 'Ghost Alternatives',
            'status' => 'published',
            'ts_language' => 'english'
        ]);

        $this->dataApi($blog, '/posts/search', [
            'search' => 'word',
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post->id);
    }

    public function testDoesNotWorkWithoutSearchQuery(): void
    {
        $blog = BlogFactory::new()->create();
        $this->dataApi($blog, '/posts/search')->assertUnprocessable();
    }

}
