<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('gets membership plan names', function() {

    Http::fake([
        'https://talk.hyvor.com/api/internal/blogs/integration/console-api' => function (Request $request) {
            dd($request->data());

            return Http::response([
                [
                    'name' => 'Free',
                ],
                [
                    'name' => 'Pro',
                ],
                [
                    'name' => 'Business',
                ]
            ]);
        }
    ]);

    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 23
    ]);

    consoleApi($blog, 'get', '/integrations/hyvor-talk/membership-plans')
        ->assertOk()
        ->assertJsonPath('0', 'Free')
        ->assertJsonPath('1', 'Pro')
        ->assertJsonPath('2', 'Business');

    Http::assertSent(function (Request $request) {

        $data = json_decode(decrypt($request->data()['message'], false), true)['data'];
        expect($data['endpoint'])->toBe('/membership-plans');
        expect($data['method'])->toBe('GET');
        expect($data['website_id'])->toBe(23);

        return true;
    });

});