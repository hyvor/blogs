<?php

namespace Tests\Unit\Domains\Post\Jobs;

use App\Domains\Post\Jobs\PostVariantUpdateTsLanguageJob;
use App\Models\Language;
use App\Models\PostVariant;
use Tests\TestCase;

class PostVariantUpdateTsLanguageJobTest extends TestCase
{

    public function testHandle()
    {

        $l = Language::factory()->create(['code' => 'en']);

        $v1 = PostVariant::factory()->create(['language_id' => $l->id, 'ts_language' => 'english']);
        $v2 = PostVariant::factory()->create(['language_id' => $l->id, 'ts_language' => 'english']);

        // other language
        $v3 = PostVariant::factory()->create(['ts_language' => 'english']);

        $l->code = 'fr';
        $l->save();

        $job = new PostVariantUpdateTsLanguageJob($l);
        app()->call([$job, 'handle']);

        $v1->refresh();
        $v2->refresh();

        $this->assertEquals('french', $v1->ts_language);
        $this->assertEquals('french', $v2->ts_language);

        $v3->refresh();
        $this->assertEquals('english', $v3->ts_language);

    }

}
