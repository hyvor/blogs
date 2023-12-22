<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'The Best Blogging Platform for ' . $name,
        'description' => 'Find out the best blogging platform for ' . $name . '. A blogging platform that is fast, secure, and easy to use and fits all needs for ' . $name . '.',
        'image' => '',
        'canonical' => 'https://blogs.hyvor.com/for/' . $slug,
    ])
</head>

<body class="for">

@include('landing.nav')

<div class="for-header">

    <div class="container">

        <h1>
            The Best Blogging Platform for {{ $name }}
        </h1>

        <h2>
            Hyvor Blogs is an all-in-one blogging platform for <b>{{ $name }}</b> to start a blog easily.
        </h2>


        @include('landing.inc.signup-button')

    </div>

</div>

<div class="wave">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<div class="for-why">

    <div class="container">

        <div class="why-title">

            <h3>Why is Hyvor Blogs the best blogging platform for {{ $name }}?</h3>

            <p>
                See the most important features of Hyvor Blogs that makes it a great blogging platform for {{ $name }}.
            </p>

        </div>

        <div class="features">

            <div class="feature-card">

                <img src="/img/landing/custom-themes.svg" alt="Custom Themes for {{ $name }} Blogs">

                <h4>Custom Themes</h4>

                <p>
                    Hyvor Blogs comes with a beautiful set of free themes, which you can easily customize. You can also create your own theme from scratch.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/host-anywhere.svg" alt="Hosting">

                <h4>Host Anywhere</h4>

                <p>
                    How your blog anywhere you want. Subdomain, custom domain, sub-directory. You can easily set up Hyvor Blogs on any of them.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/multi-language.svg" alt="Multi Language Blogging for {{ $name }}">

                <h4>Multi-language</h4>

                <p>
                    Reach a wider audience by writing in multiple languages. There is no other platform that makes multi-language blogging this easy.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/seo.svg" alt="In-Built SEO">

                <h4>In-built SEO</h4>

                <p>
                    Hyvor Blogs handles all technical SEO stuff for you. You just have to write great content and stay ahead of your peer {{  strtolower($name) }}.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/ai.svg" alt="Artificial Intelligence">

                <h4>Enriched with AI</h4>

                <p>
                    Hyvor Blogs provides AI-powered tool to translate your posts into more than 30+ language with a single click.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/team.svg" alt="For {{ $name }} teams">

                <h4>Team-friendly</h4>

                <p>
                    Invite your friends and team members to write on your blog. You can also set permissions for them.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/fast.svg" alt="Fast blog for {{ $name }}">

                <h4>Super Fast</h4>

                <p>
                    You have nothing to do. Hyvor Blogs is super fast! Not sure? Check <a href="https://hyvor.com/blog">our blog</a> (powered by Hyvor Blogs) see how fast it is.
                </p>

            </div>

            <div class="feature-card">

                <img src="/img/landing/data.svg" alt="Full ownership & control">

                <h4>Full Ownership and Control</h4>

                <p>
                    Unlike other locked-in platforms, Hyvor Blogs gives you full ownership and control over your blog and data. You decide what happens on your blog.
                </p>

            </div>

        </div>

        <div class="reviews-wrap">
            @include('landing.inc.reviews')
        </div>


        <p style="width: 500px;max-width: 100%;margin: auto;">
            Hyvor Blogs is the best blogging platform to start a blog for {{ $name }}. Are you ready to start yours?
        </p>

        @include('landing.inc.signup-button', ['buttonName' => 'Start My Blog'])

    </div>

</div>

@include('landing.footer')

</body>
</html>