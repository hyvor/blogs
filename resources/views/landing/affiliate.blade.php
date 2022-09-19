<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'Hyvor Blogs - Affiliate Program',
        'description' => '',
        'image' => '',
        'canonical' => 'https://blogs.hyvor.com/affiliate',
    ])
</head>

<body class="affiliate">

@include('landing.nav')

<div class="aff-container">

    <div class="container">
        <h1>Hyvor Blogs Affiliate Program</h1>

        <p>Earn 30% commission from all payments made by customers you refer.</p>

        <br>

        <a href="https://hyvorblogs.getrewardful.com/signup" class="button" rel="nofollow">
            Join Affiliate Program
        </a>
    </div>
</div>
<div class="wave">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<div class="faqs">

    <div class="faq">
        <h3>How does this work?</h3>
        <p>
            <ul>
                <li>Join our affiliate program</li>
                <li>Share the URL you get</li>
                <li>Earn commissions from the users who sign up via your URL</li>
            </ul>
        </p>
    </div>


    <div class="faq">
        <h3>Where can I share the URL?</h3>
        <p>You can share your affiliate URL on your blog, vlogs, social media channels, newsletters, etc. However, you should not use your affiliate URL in paid ads (search engine ads, social media ads, etc.).</p>
    </div>

    <div class="faq">
        <h3>How do commissions work?</h3>
        <p>
            You will receive a 30% commission from all payments made by customers you refer to Hyvor Blogs. Commissions are locked for 30 days (for refund reasons) before making them eligible for payout.
        </p>
    </div>

    <div class="faq">
        <h3>How do payouts work?</h3>
        <p>
            We send payouts via PayPal each month.
        </p>
    </div>


</div>

<div class="button-main">
    <a href="https://hyvorblogs.getrewardful.com/signup" class="button big" rel="nofollow">
        Join Affiliate Program
    </a>
</div>


@include('landing.footer')

</body>