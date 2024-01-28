<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Data\Enums\JobStatusEnum;
use App\Models\Export;
use App\Models\Media;
use App\Models\Navigation;
use App\Models\Redirect;

it('exports in Hyvor Blogs format', function() {

    $blog = blogWithAccess();
    $blog2 = blog();

    $primaryLanguage = addPrimaryLanguage($blog); addPrimaryLanguage($blog2);
    addLanguage($blog);

    $blogVariants = addBlogVariants($blog);

    $posts = addPosts($blog, 10); addPosts($blog2, 2);
    $users = addUsers($blog, 5); addUsers($blog2, 2);
    $tags = addTags($blog, 5); addTags($blog2, 2);

    $media = Media::factory()->count(3)->create(['blog_id' => $blog->id]);
    Media::factory()->count(2)->create(['blog_id' => $blog2->id]);

    $navigation = Navigation::factory()->count(4)->create(['blog_id' => $blog->id]);
    Navigation::factory()->count(1)->create(['blog_id' => $blog2->id]);

    addDefaultRoutes($blog); addDefaultRoutes($blog2);

    Redirect::factory()->count(5)->create(['blog_id' => $blog->id]);
    Redirect::factory()->count(2)->create(['blog_id' => $blog2->id]);

    consoleApi($blog, 'POST', '/data/export')->assertOk();

    $export = Export::where('blog_id', $blog->id)->first();

    expect($export->status)->toBe(JobStatusEnum::COMPLETED);
    expect($export->format)->toBe(ExportFormatEnum::HYVOR_BLOGS);
    expect($export->url)->toBeString();

    $date = date('Y-m-d');
    $path = storage_path("/app/exports/$blog->id/$date-$export->id.json");
    $data = json_decode(file_get_contents($path), true);

    expect($data['blog']['id'])->toBe($blog->id);
    expect($data['blog']['subdomain'])->toBe($blog->subdomain);
    expect($data['blog']['variants'][0]['name'])->toBe($blogVariants[0]['name']);

    expect($data['languages'])->toHaveCount(2);
    expect($data['languages'][0]['id'])->toBe($primaryLanguage->id);

    expect($data['posts'])->toHaveCount(10);
    expect($data['posts'][0]['id'])->toBe($posts[0]->id);
    expect($data['posts'][0]['variants'][0]['title'])->toBe($posts[0]->variants[0]->title);
    expect($data['posts'][0]['variants'][0]['content_html'])->toBe($posts[0]->variants[0]->content_html);

    expect($data['users'])->toHaveCount(6); // seeded + access
    expect($data['users'][1]['id'])->toBe($users[0]->id);
    expect($data['users'][1]['variants'][0]['name'])->toBe($users[0]->variants[0]->name);

    expect($data['tags'])->toHaveCount(5);
    expect($data['tags'][0]['id'])->toBe($tags[0]->id);
    expect($data['tags'][0]['variants'][0]['name'])->toBe($tags[0]->variants[0]->name);

    expect($data['media'])->toHaveCount(3);
    expect($data['media'][0]['id'])->toBe($media[0]->id);

    expect($data['navigation'])->toHaveCount(4);
    expect($data['navigation'][0]['id'])->toBe($navigation[0]->id);

    expect($data['redirects'])->toHaveCount(5);
    expect($data['redirects'][0]['id'])->toBe($blog->redirects[0]->id);

    expect($data['routes'])->toHaveCount(5);
    expect($data['routes'][0]['id'])->toBe($blog->routes[0]->id);
    expect($data['routes'][0]['name'])->toBe($blog->routes[0]->name);

});