<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('gets membership plan names', function() {

    Http::fake([
        'https://talk.hyvor.cluster/api/internal/blogs/integration/console-api' => Http::sequence()
            ->push([
                'memberships_enabled' => true,
                'memberships_currency' => 'USD',
            ])
            ->push([
                [
                    'name' => 'Free',
                    'monthly_price' => 10,
                ],
                [
                    'name' => 'Pro',
                    'monthly_price' => 20,
                ],
                [
                    'name' => 'Business',
                    'monthly_price' => 30,
                ]
            ])
    ]);


    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 23
    ]);

    consoleApi($blog, 'get', '/integrations/hyvor-talk/membership-plans')
        ->assertOk()
        ->assertJsonPath('currency', 'USD')
        ->assertJsonPath('plans.0.name', 'Free')
        ->assertJsonPath('plans.1.name', 'Pro')
        ->assertJsonPath('plans.2.name', 'Business');

    Http::assertSent(function (Request $request) {

        $data = json_decode(decrypt($request->data()['message'], false), true)['data'];

        if ($data['endpoint'] === '/website') {
            return true;
        }

        expect($data['endpoint'])->toBe('/membership-plans');
        expect($data['method'])->toBe('GET');
        expect($data['website_id'])->toBe(23);

        return true;
    });

});
