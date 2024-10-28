<?php

namespace Tests\Feature\ConsoleAPI\Media;

use App\Domains\Blog\Jobs\UpdateMediaLinkJob;
use App\Models\Media;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('updates name and moves file',function() {

    Queue::fake();

    $blog = blogWithAccess();

    Storage::fake();
    Storage::put('blog/' . $blog->id . '/test.png', 'content');

    $media = Media::factory()->create([
        'blog_id' => $blog->id,
        'name' => 'test.png',
    ]);

    consoleApi($blog, 'PATCH', '/media/' . $media->id, [
        'name' => 'new-name.png'
    ])
        ->assertOk()
        ->assertJsonPath('name', 'new-name.png');

    expect($media->refresh()->name)->toBe('new-name.png');

    Storage::assertMissing('blog/' . $blog->id . '/test.png');
    Storage::assertExists('blog/' . $blog->id . '/new-name.png');

    Queue::assertPushed(UpdateMediaLinkJob::class);

});