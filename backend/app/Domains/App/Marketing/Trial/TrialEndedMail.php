<?php declare(strict_types=1);

namespace App\Domains\App\Marketing\Trial;

use App\Models\Blog;
use Hyvor\Internal\Auth\AuthUser;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialEndedMail extends Mailable
{

    public function __construct(
        public Blog $blog,
        public AuthUser $user
    ) {}

    public function envelope() : Envelope
    {
        return new Envelope(
            from: new Address('blogs.support@hyvor.com', 'Hyvor Blogs'),
            subject: 'Your Hyvor Blogs trial has ended'
        );
    }

    public function content() : Content
    {
        return new Content(
            view: 'emails.trial-ended'
        );
    }

}