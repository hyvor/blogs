<?php
namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Webhook;

it('deletes webhook', function() {

    $webhook = Webhook::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('DELETE', "/webhook/$webhook->id")
        ->assertOk();

    expect(Webhook::find($webhook->id))->toBeNull();

});