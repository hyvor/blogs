<?php

namespace Tests\Unit\Domains\Post\Listeners;

use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Post\Listeners\PostVariantUpdateContentHtmlListener;
use App\Domains\Post\Listeners\PostVariantUpdateWordCountListener;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;

it('is attached', function () {
    Event::fake();

    Event::assertListening(
        PostVariantUpdatedEvent::class,
        PostVariantUpdateContentHtmlListener::class
    );
    Event::assertListening(
        PostVariantUpdatedEvent::class,
        PostVariantUpdateWordCountListener::class
    );
});

it('updates content HTML', function () {
    $variant = PostVariant::factory()->create([
        'content' => json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello World',
                        ],
                    ],
                ],
            ],
        ]),
        'content_html' => null
    ]);

    $event = new PostVariantUpdatedEvent($variant, $variant);
    $listener = new PostVariantUpdateContentHtmlListener();
    $listener->handle($event);

    $variant->refresh();
    expect($variant->content_html)->toBe('<p>Hello World</p>');
    expect($variant->content_text)->toBe('Hello World');

});

it('updates words count', function () {
    $variant = PostVariant::factory()->create([
        'content' => json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello World',
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $event = new PostVariantUpdatedEvent($variant, $variant);
    $listener = new PostVariantUpdateWordCountListener();
    $listener->handle($event);

    expect($variant->refresh()->words)->toBe(2);
});
