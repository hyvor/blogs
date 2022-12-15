<?php

namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Webhook;

it('deletes webhook', function () {
    $blog = blogWithAccess();
    $webhook = Webhook::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'DELETE', "/webhook/$webhook->id")
        ->assertOk();

    expect(Webhook::find($webhook->id))->toBeNull();
});
