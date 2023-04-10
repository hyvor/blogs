<?php

namespace Tests\Unit\Jobs\Language;

use App\Domains\Language\Jobs\DeleteLanguageVariants;
use App\Models\BlogVariant;
use App\Models\Language;
use App\Models\NavigationVariant;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\TagVariant;
use App\Models\UserVariant;

it('deletes all variants', function () {
    $blog = blog();
    $language = Language::factory()->create(['blog_id' => $blog]);

    $id = rand(1000, 2000);

    BlogVariant::factory()->create(['blog_id' => $blog, 'language_id' => $language]);
    Post::factory()->create(['id' => $id]);
    PostVariant::factory()->create(['post_id' => $id, 'language_id' => $language]);
    UserVariant::factory()->create(['user_id' => $id, 'language_id' => $language]);
    TagVariant::factory()->create(['tag_id' => $id, 'language_id' => $language]);
    NavigationVariant::factory()->create(['navigation_id' => $id, 'language_id' => $language]);

    expect(BlogVariant::where(['language_id' => $language->id])->count())->toBe(1);
    expect(PostVariant::where(['language_id' => $language->id])->count())->toBe(1);
    expect(UserVariant::where(['language_id' => $language->id])->count())->toBe(1);
    expect(TagVariant::where(['language_id' => $language->id])->count())->toBe(1);
    expect(NavigationVariant::where(['language_id' => $language->id])->count())->toBe(1);

    $job = new DeleteLanguageVariants($language);
    $job->handle();

    expect(BlogVariant::where(['language_id' => $language->id])->count())->toBe(0);
    expect(PostVariant::where(['language_id' => $language->id])->count())->toBe(0);
    expect(UserVariant::where(['language_id' => $language->id])->count())->toBe(0);
    expect(TagVariant::where(['language_id' => $language->id])->count())->toBe(0);
    expect(NavigationVariant::where(['language_id' => $language->id])->count())->toBe(0);
});
