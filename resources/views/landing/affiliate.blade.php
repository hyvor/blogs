<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'Hyvor Blogs Affiliate Program',
        'description' => 'Join Hyvor Blogs affiliate program and earn 15% recurring commission for all referred users',
        'canonical' => 'https://blogs.hyvor.com/affiliate',
    ])

    <style>
        .questions {
            width: 600px;
            margin: auto;
            max-width: 100%;
        }

        .question {
            margin-bottom: 40px;
        }
    </style>
</head>

<?php
$check = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg>';
?>

<body class="for">

@include('landing.nav')

<div class="for-header">

    <div class="container">

        <h1>
            Hyvor Blogs Affiliate Program
        </h1>

        <h2>
            Join Hyvor Blogs affiliate program and earn 15% recurring commission from all referred users, forever!
        </h2>


        <div class="signup-button">
            <a data-flashload-skip-link href="/affiliate-signup" class="button big">
                Become an Affiliate
            </a>
            <div class="offer">

                <div>{!! $check !!} <span>15% recurring commissions</span></div>
                <div>{!! $check !!} <span>Wise & Paypal payouts</span></div>

            </div>
        </div>

    </div>

</div>

<div class="wave">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<div class="container questions">

    <div class="question">

        <h2>What is Hyvor Blogs?</h2>

        <p>
            Hyvor Blogs is a multi-language blogging platform to start a fully-customizable blog. Custom themes, custom domains, in-built SEO, blazing-fast design, a carefully crafted rich editor, AI-powered translations, and many other features are included.
        </p>

    </div>

    <div class="question">

        <h2>Who can join our affiliate program?</h2>

        <p>
            Anyone who has an audience can join our affiliate program, promote Hyvor Blogs and earn a commission. Ex: bloggers, youtubers, developers, etc.
        </p>

    </div>

    <div class="question">

        <h2>How do referrals work?</h2>

        <p>
            Once you sign up as an affiliate, you will get a unique URL linking to our homepage <b>blogs.hyvor.com</b>. When someone visits our website through that link and signs up to a paid plan within <b>90 days</b>, you will receive a 15% commission all payments made by the referred user.
        </p>

    </div>

    <div class="question">

        <h2>How do payouts work?</h2>

        <p>
            We will hold commissions for 30 days (in case of refunds). We will then send them to your Wise account if the minimum threshold of <b>$50</b> is exceeded.
        </p>

    </div>

</div>

<div class="signup-button" style="margin-top: 100px">
    <a data-flashload-skip-link href="/affiliate-signup" class="button big">
        Become an Affiliate
    </a>
    <div class="offer">

        <div>{!! $check !!} <span>15% recurring commissions</span></div>
        <div>{!! $check !!} <span>Wise & Paypal payouts</span></div>

    </div>
</div>

@include('landing.footer')

</body>
</html>