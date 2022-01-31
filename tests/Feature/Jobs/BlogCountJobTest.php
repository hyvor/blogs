<?php
namespace Tests\Feature\Jobs;

use App\Jobs\Scheduled\Counts\BlogCountJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCountJobTest extends TestCase {

    use RefreshDatabase;

    public function test() {
        dispatch(new BlogCountJob);
    }

}