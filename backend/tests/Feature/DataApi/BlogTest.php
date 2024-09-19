<?php

namespace Tests\Feature\DataApi;

use App\Models\Subscription;
use Illuminate\Testing\Fluent\AssertableJson;

it('fetches blog', function () {

    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));

    dataApi($blog, '/blog')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('subdomain')
               ->has('name')
               ->etc();
        });

});

it('does not fetch invalid blogs', function () {
    dataApi('testing-other', '/blog')->assertNotFound();
});

it('fetches blog with correct language', function () {

    $blog = blog();
    $variant = addBlogVariants($blog, addPrimaryLanguage($blog))[0];

    dataApi($blog, '/blog', [
            'language' => $variant->language->code,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($variant) {
            $json->where('name', $variant->name)
                ->etc();
        });

});

it('filters key', function () {

    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));

    dataApi($blog, '/blog', [
            'keys' => 'subdomain',
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('subdomain')
                ->missing('name');
        });

});

describe('branding in foot code', function() {

    it('adds branding for a blog in trial', function() {

        $blog = blog();
        addBlogVariants($blog, addPrimaryLanguage($blog));

        $codeFoot = dataApi($blog, '/blog')
            ->assertOk()
            ->json()['code_foot'];

        expect($codeFoot)->toContain('<a href="https://blogs.hyvor.com?source=branding&subdomain=' . $blog->subdomain . '" target="_blank"');

    });

    it('does not add for dev blogs', function() {

        $blog = blog([
            'type' => 'dev',
            'meta' => json_encode(['code_foot' => '<p>foot</p>'])
        ]);
        addBlogVariants($blog, addPrimaryLanguage($blog));

        $codeFoot = dataApi($blog, '/blog')
            ->assertOk()
            ->json()['code_foot'];

        expect($codeFoot)->toBe('<p>foot</p>');
        expect($codeFoot)->not()->toContain('hyvor');

    });

    it('adds to starter blogs', function() {

        $blog = blog([
            'meta' => json_encode(['code_foot' => '<p>foot</p>'])
        ]);
        Subscription::factory()->create([
            'blog_id' => $blog->id,
            'plan' => 'starter'
        ]);
        addBlogVariants($blog, addPrimaryLanguage($blog));

        $codeFoot = dataApi($blog, '/blog')
            ->assertOk()
            ->json()['code_foot'];

        expect($codeFoot)->toStartWith('<p>foot</p>');
        expect($codeFoot)->toContain('<a href="https://blogs.hyvor.com?source=branding');

    });

    it('does not add to growth blogs', function() {

        $blog = blog();
        Subscription::factory()->create([
            'blog_id' => $blog->id,
            'plan' => 'growth'
        ]);
        addBlogVariants($blog, addPrimaryLanguage($blog));

        $codeFoot = dataApi($blog, '/blog')
            ->assertOk()
            ->json()['code_foot'];

        expect($codeFoot)->toBeNull();

    });

    it('respects meta', function() {

        $blog = blog([
            'meta' => json_encode([
                'hb_branding' => false,
                'code_foot' => '<p>foot</p>',
            ])
        ]);
        addBlogVariants($blog, addPrimaryLanguage($blog));

        $codeFoot = dataApi($blog, '/blog')
            ->assertOk()
            ->json()['code_foot'];

        expect($codeFoot)->toBe('<p>foot</p>');
        expect($codeFoot)->not()->toContain('hyvor');

    });

});