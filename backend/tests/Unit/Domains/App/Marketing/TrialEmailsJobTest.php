<?php

namespace Tests\Unit\Domains\App\Marketing;

use App\Domains\App\Marketing\Trial\TrialEmailsJob;
use App\Domains\App\Marketing\Trial\TrialEndedMail;
use App\Domains\App\Marketing\Trial\TrialEndingMail;
use App\Models\Blog;
use Hyvor\Internal\Auth\Providers\Fake\FakeProvider;
use Illuminate\Support\Facades\Mail;

it('sends trial ending emails', function() {

    Mail::fake();

    FakeProvider::databaseSet([
        [
            'id' => 10,
            'email' => 'test@hyvor.com',
            'name' => 'John'
        ]
    ]);

    $blog = Blog::factory()->create([
        'trial_ends_at' => now()->addHours(23),
        'hyvor_user_id' => 10
    ]);

    // dev blog
    Blog::factory()->create([
        'trial_ends_at' => now()->addHours(23),
        'type' => 'dev'
    ]);

    // blog with trial ends in 25 hours
    Blog::factory()->create([
        'trial_ends_at' => now()->addHours(25),
        'hyvor_user_id' => 10
    ]);

    $job = new TrialEmailsJob();
    $job->handle();

    Mail::assertSentCount(1);
    Mail::assertSent(function (TrialEndingMail $mail) use ($blog) {
        expect($mail->hasTo('test@hyvor.com'))->toBeTrue();
        expect($mail->blog->subdomain)->toBe($blog->subdomain);
        expect($mail->user->name)->toBe('John');
        return true;
    });

});

it('sends trial ended email', function() {

    Mail::fake();

    FakeProvider::databaseSet([
        [
            'id' => 10,
            'email' => 'test@hyvor.com',
            'name' => 'John'
        ]
    ]);

    $blog = Blog::factory()->create([
        'trial_ends_at' => now()->subHours(2),
        'hyvor_user_id' => 10
    ]);

    // dev blog
    Blog::factory()->create([
        'trial_ends_at' => now()->addHours(23),
        'type' => 'dev'
    ]);

    // blog with trial ended 25 hours ago
    Blog::factory()->create([
        'trial_ends_at' => now()->subHours(25),
        'hyvor_user_id' => 10
    ]);

    $job = new TrialEmailsJob();
    $job->handle();

    Mail::assertSentCount(1);
    Mail::assertSent(function (TrialEndedMail $mail) use ($blog) {
        expect($mail->hasTo('test@hyvor.com'))->toBeTrue();
        expect($mail->blog->subdomain)->toBe($blog->subdomain);
        expect($mail->user->name)->toBe('John');
        return true;
    });

});