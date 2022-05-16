<?php

namespace Tests\Unit\Import;

use App\Models\Blog;
use App\Models\Import;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Domains\Import\Importer;
use App\Domains\Import\Repository;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use Faker\Factory;

use Illuminate\Foundation\Testing\RefreshDatabase;
 
uses(RefreshDatabase::class);

// php artisan test  --filter 'ImporterTest'
// use the blog() to get blog data


beforeEach(function() {
    $this->repo = new Repository();

    // dd(blog());
    $faker = Factory::create();

    // $this->import = Import::factory()->create();

    // $this->import = Import::factory()->count(1)->create([
    //     'blog_id' => 1, 
    //     'name' => $faker->name,
    //     'type' => 'wordpress',
    //     'status' => 'success',
    // ]);

    $this->blog = [
        'id' => 1, 
        'subdomain ' => 'test',
        'type' => 'default',
        'hosting_at' => 'subdomain',
    ];
});

it('get authors from the repository and save it in the database', function() {
    // dd('hello world');
    $role = UserRoleEnum::from('editor');
    $status = UserStatusEnum::from('active');

    // dd($this->import);
    dd(blog());
    
    $this->repo->author(
        id: 1,
        name: 'rasif',
        role: $role,
        status: $status,
        slug: 'test',
        email: 'rasif@hyvor.com'
    );

    // $importer = new Importer($repo, $blog, $import);

    // dd($importer);
});

// it('get tags from the repository and save it in the database', function() {
//     dd($this->hey);
// });

// it('get posts and pages from the repository and save it in the database', function() {});