<?php
namespace Tests\Feature\DataAPI;

use App\Domains\Post\PostSearchRepository;
use App\Models\Blog;
use App\Models\PostVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

/**
 * Testing search is not easy
 * Seeding meilisearch is asynchronous
 * The Collection driver does not support ->where()
 * So, only statuses are checked
 */

beforeEach(function() {
    
    $post = blog()->posts()->where('is_page', false)->first();
    $variants = $post->variants;
    
    $variants[0]->update(['title' => "English", 'status' => 'published']);
    $variants[1]->update(['title' => "French", 'status' => 'published']);
    
});

it('searches posts', function() {

    $this
        ->callDataApi('/posts/search', [
            'search' => "English"
        ])
        ->assertStatus(200);
    
});

it('does not work without search query', function() {
   
    $this
        ->callDataApi('/posts/search')
        ->assertStatus(400);
    
});
