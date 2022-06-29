<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Models\Navigation;

it('sorts', function () {
    $navs = Navigation::factory()->count(3)->create(['blog_id' => blog()]);

    $this->callConsoleApi('PATCH', '/navigations/sort', [
        'ids' => $navs->map(fn ($nav) => $nav->id)->toArray(),
    ])->assertOk();

    expect($navs[0]->refresh()->sort)->toBe(1);
    expect($navs[1]->refresh()->sort)->toBe(2);
    expect($navs[2]->refresh()->sort)->toBe(3);
});
