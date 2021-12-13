<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'Hyvor Blogs - The simplest blogging platform',
        'description' => 'Hyvor Blogs is the simplest blogging platform',
        'image' => '',
        'canonical' => 'https://blogs.hyvor.com',
    ])
</head>

<body class="index">


@include('landing.nav')

<section id="hero-home">

    <div class="container">

        <div class="title-and-svg">

            <div class="hero-title">
                <h1>
                    A simple blogging platform - nothing more!
                </h1>
                <h2>
                    Hyvor Blogs is a platform to start a blog that you own, with customizable themes and a custom domain.
                </h2> 
                <a data-no-instant href="/console?signup=1" class="button big">
                    Start a Blog for Free
                </a>

                {{-- <div class="hero-message-wrap">
                    <a 
                        class="hero-message"
                        href="/blog/v2"
                        target="_blank"
                    >
                        Hyvor Talk v2 is now generally available &rsaquo;&rsaquo;
                    </a>
                </div> --}}
            </div>

            <div class="hero-svg">



            </div>

        </div>
    </div>
</section>

<div class="wave">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#f1e8e8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<section class="feature container">

    <div class="feature-image">
        <img src="/img/landing/home-speed.svg" />
    </div>
    <div class="feature-description">
        <h3>Fast</h3>
        <p>We serve your blog through our global CDN <span class="highlight">in plain HTML</span> - no Javascript-based rendering. A website cannot be faster than that. Additionally, our strict theme guidelines make sure that all themes are built be fast, without any bloated scripts.</p>
        <p>We use InstantClick.js under the hood to make the navigation between pages faster. It is the same technology we use in our landing pages (Try clicking the Pricing link above to see how it loads the other page lighting-fast without reloading the browser).</p>
    </div>

</section>

<section class="feature container">
    
    <div class="feature-description">
        <h3>Secure & Private</h3>
        <p>You are the owner of the content. We just host your blog and allow you to edit content. We never sell your data to any third-party. We never inject any tracking scripts or advertisements into your blog.</p>
        <p>We allow exporting all content in the WordPress format, making it easy to move to another platform whenever you feel Hyvor Blogs is not the choice.</p>
    </div>

    <div class="feature-image">
        <img src="/img/landing/home-ownership.svg" />
    </div>

</section>

<section class="feature container">

    <div class="feature-image">
        <img src="/img/landing/home-editor.svg" />
    </div>
    <div class="feature-description">
        <h3>Easy Editing & Publishing</h3>
        <p>You can multiple blogs one place: Console. It provides a friendly and powerful Rich Text Editor that supports image uploading, embedding content, etc. You get a beautiful interface to publish/schedule posts, write drafts, edit post meta/SEO data, etc.</p>
    </div>

</section>

<section class="feature container">
    
    <div class="feature-description">
        <h3>Custom Themes & Domain</h3>
        <p>You can select a theme from our Themes Marketplace and edit all its files in the Console. Your can even build a completely new theme from scratch right from the Console.</p>
        <p>By default, you get a hyvorblogs.io subdomain for your blog. You can also point a custom domain to your blog.</p>
    </div>

    <div class="feature-image">
        <img src="/img/landing/home-custom.svg" />
    </div>

</section>

<section class="feature container">

    <div class="feature-image">
        <img src="/img/landing/home-code.svg" />
    </div>
    <div class="feature-description">
        <h3>Code Injecting</h3>
        <p>You can inject code into your whole blog or just for some posts. This is useful for adding Analytics, Comments, Email Subscription Forms, etc.</p>
        <p>Premium plan of the <a href="https://talk.hyvor.com" class="link"target="_blank">Hyvor Talk</a> commenting system is free up to 100,000 pageviews per month for all Hyvor Blogs users.
        </p>
    </div>

</section>

<section class="feature container">

    <div class="feature-description">
        <h3>In-built SEO</h3>
        <p>We handle all technical aspects of search engine indexing and social media previews.</p>

        <ul>
            <li>Adding SEO tags like title, description, canonical URL to all pages</li>
            <li>Generating a sitemap.xml</li>
            <li>Setting up OG and Twitter tags that are useful to generate a better preview on social media platforms</li>
        </ul>
    </div>


    <div class="feature-image">
        <img src="/img/landing/home-seo.svg" />
    </div>

</section>

<section class="feature container">


    <div class="feature-image">
        <img src="/img/landing/home-image.svg" />
    </div>

    
    <div class="feature-description">
        <h3>For your whole team</h3>
        <p>
            Hyvor Blogs provides a team plan for teams and companies. You can add your teammates to your blog as Editors, Writers, etc. You can also invite outside contributors to your blog.
        </p>
        <p>
            Each of your teammate wil need a Hyvor account to access the console. Enterprise blogs can set up SAML-based Single Sign-on.
        </p>
    </div>


</section>

{{-- <section class="feature container">

    
    <div class="feature-description">
        <h3>Custom Media Hosting</h3>
        <p>
            By default, media uploads are hosted in our servers. We allow a few images types (PNG, JPEG, and GIF) under the storage limits shown in the Pricing page. For large organizations who  higher limits and to host videos, you can connect the following services (requires a seperate subscription on their platforms).
            <ul>
                <li>AWS S3 (or any S3-compatible storage like DigitalOcean Spaces)</li>
                <li>Backblaze B2</li>
                <li>Azure Blob Storage</li>
                <li>Cloudinary</li>
            </ul>
        </p>
        <p>Alternatively, you can also upload your videos to a third-party platform (Ex: Youtube/Vimeo) and embed them in your posts.</p>
    </div>

    <div class="feature-image">
        <img src="/img/landing/home-image.svg" />
    </div>

</section> --}}

<section class="feature container">


    <div class="feature-description">
        <h3>Beyond Death</h3>
        <p>Don't let your blog die with you. Once your death is reported to us by your family, we will archive your blog, cancel your subscription, and host your blog for free forever - no subscription burden on your family.</p>
        <p>We are also happy to host any deceased person's personal blog on our platform for a one-time charge. Contact us for more information.</p>
    </div>

    <div class="feature-image">
        <img src="/img/landing/home-death.svg" />
    </div>

</section>


@include('landing.footer')

</body>
</html>