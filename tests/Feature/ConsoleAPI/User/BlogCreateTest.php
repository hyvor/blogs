<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Redirect;

class BlogCreateTest extends TestCase
{
    use RefreshDatabase;

    private function callBlogCreateApi($data) {
        return $this->callConsoleUserApi('post', '/blog', $data);
    }

    public function testBlogCreationSuccess() {
        $this->callBlogCreateApi([
            'name' => 'another test blog',
            'subdomain' => 'another-test'
        ])->assertOk()->assertJson(function ($json) {
            $json->has('user');
            $json->has('blog');
        });
    }

    public function testBlogCreationDuplicateSubdomain() {
        $this->callBlogCreateApi([
            'name' => 'A name',
            'subdomain' => 'test' // this is duplicate from seeder
        ])->assertUnprocessable();
    }

    public function testEmptyName() {
        $this->callBlogCreateApi([
            'subdomain' => 'test'
        ])->assertUnprocessable();
    }

    public function testEmptySubdomain() {
        $this->callBlogCreateApi([
            'name' => 'some name'
        ])->assertUnprocessable();
    }

    /**
     * Todo: Add console API testing to make sure all default data are added.
     */
    

}
