<?php

$svgCheck = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#896c6b" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg>';
$svgCancel = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ccc" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
</svg>';

$svgInfo = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
</svg>';

$usersInfo = <<<HTML
<span class="info">
    <span class="icon">$svgInfo</span>
    <div class="info-popup">
        Total number of users who writes for your blog (Your team members).
    </div>
</span>
HTML;

$storageInfo = <<<HTML
<span class="info">
    <span class="icon">$svgInfo</span>
    <div class="info-popup">
        Total size of images and other media uploaded to your blog.
    </div>
</span>
HTML;

$autoTranslateInfo = <<<HTML
<span class="info">
    <span class="icon">$svgInfo</span>
    <div class="info-popup">
        Maximum number of characters that can be auto-translated per month via DeepL.
    </div>
</span>
HTML;


$pricingRow = "<tr>
                <th></th>
                <th>Price</th>
                <th>Users $usersInfo</th>
                <th>Storage $storageInfo</th>
                <th>Auto-translate $autoTranslateInfo</th>
            </tr>";

?>

<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'Hyvor Blogs - Pricing',
        'description' => '',
        'image' => '',
        'canonical' => 'https://blogs.hyvor.com/pricing',
    ])
</head>

<body class="pricing">

@include('landing.nav')


<div class="pricing-table-new">

    <div class="container">

        <div class="">

            <h1>
                Simple & transparent pricing
            </h1>

            <h2>
                7-day free trial. Cancel anytime.
            </h2>

        </div>

        <div class="pricing-table-top">

            <div class="more-plans-wrap inner">

                <div class="more-plans-left">&nbsp;</div>

                <div class="more-plans-right">
                    <div class="l">
                        <button 
                            class="button text-only small more-plans-left-button"
                            onclick="decreaseSection()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-caret-left" viewBox="0 0 16 16">
                                <path d="M10 12.796V3.204L4.519 8 10 12.796zm-.659.753-5.48-4.796a1 1 0 0 1 0-1.506l5.48-4.796A1 1 0 0 1 11 3.204v9.592a1 1 0 0 1-1.659.753z"/>
                            </svg> Lower <span class="plans-keyword">Plans</span>
                        </button>
                    </div>
                    <div class="r">
                        <button 
                            class="button text-only small more-plans-right-button"
                            onclick="increaseSection()"
                        >
                            Higher <span class="plans-keyword">Plans</span> <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-caret-right" viewBox="0 0 16 16">
                                <path d="M6 12.796V3.204L11.481 8 6 12.796zm.659.753 5.48-4.796a1 1 0 0 0 0-1.506L6.66 2.451C6.011 1.885 5 2.345 5 3.204v9.592a1 1 0 0 0 1.659.753z"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

            <div class="inner">

                <div class="feature-names">

                    <div class="billing-frequency">
                        {{-- Billed monthly --}}
                    </div>

                </div>

                <div class="plan">

                    <div class="plan-details">
                        <div class="plan-name">Starter</div>
                        <div class="plan-price">
                            <span class="price">$9</span><span class="freq">/month</span>
                        </div>
                    </div>

                    <div></div>

                </div>

                <div class="plan">

                    <div class="plan-details">
                        <div class="plan-name">Growth</div>
                        <div class="plan-price">
                            <span class="price">$19</span><span class="freq">/month</span>
                        </div>
                    </div>

                    <div></div>

                </div>

                <div class="plan">

                    <div class="plan-details">
                        <div class="plan-name">Premium</div>
                        <div class="plan-price">
                            <span class="price">$49</span><span class="freq">/month</span>
                        </div>
                    </div>

                    <div></div>

                </div>

                <div class="plan">

                    <div class="plan-details">
                        <div class="plan-name">Team</div>
                        <div class="plan-price">
                            <span class="price">$299</span><span class="freq">/month</span>
                        </div>
                    </div>

                    <div></div>

                </div>

                <div class="plan">

                    <div class="plan-details">
                        <div class="plan-name">Business</div>
                        <div class="plan-price">
                            <span class="price">$699</span><span class="freq">/month</span>
                        </div>
                    </div>

                    <div></div>

                </div>

                <div class="plan">

                    <div class="plan-details">
                        <div class="plan-name">Enterprise</div>
                        <div class="plan-price">
                            <span class="price">$1299</span><span class="freq">/month</span>
                        </div>
                    </div>

                    <div></div>

                </div>


            </div>

        </div>

        <div class="pricing-table-features">

            <div class="inner">

                <div class="feature-title">
                    Basic Features
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Users
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Total number of users who writes for your blog (Your team members)
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div>2</div></div>
                    <div class="feature-value"><div>5</div></div>
                    <div class="feature-value"><div>15</div></div>
                    <div class="feature-value"><div>100</div></div>
                    <div class="feature-value"><div>1000</div></div>
                    <div class="feature-value"><div>Unlimited</div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Storage
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Total storage used for blog media (mostly uploaded images)
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div>1GB</div></div>
                    <div class="feature-value"><div>40GB</div></div>
                    <div class="feature-value"><div>250GB</div></div>
                    <div class="feature-value"><div>1TB</div></div>
                    <div class="feature-value"><div>2TB</div></div>
                    <div class="feature-value"><div>5TB</div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Custom themes
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Use default themes for free or build your own custom theme
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Custom domain
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Host your blog on your own domain
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Multi-language support
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Add multiple languages to your blog
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        SEO Analysis
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                In-post SEO analysis (check keywords, content, etc.)
                                <img src="/img/landing/pricing/seo-feature.png" alt="SEO Analysis" />
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCancel ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Link Analysis
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Post link analysis, bi-weekly full-blog link analysis, and email reports
                                <img src="/img/landing/pricing/links-feature.png" alt="Link Analysis" />
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCancel ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Data Ownership
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                You own everything you write. Export and move to another platform anytime.
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>


                <div class="feature-title">
                    AI
                </div>

                <div class="feature">
                    <div class="feature-name">
                        GPT Writing
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Use OpenAI GPT 3.5 for content writing, keyword generation, and more. Usually, 1000 tokens is about 750 words.
                                <img src="/img/landing/pricing/gpt-feature.png" alt="GPT Writing" />
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCancel ?></div></div>
                    <div class="feature-value"><div>100k tokens/m</div></div>
                    <div class="feature-value"><div>1m tokens/m</div></div>
                    <div class="feature-value"><div>3m tokens/m</div></div>
                    <div class="feature-value"><div>15m tokens/m</div></div>
                    <div class="feature-value"><div>30m tokens/m</div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Auto-Translations
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Automatically translate your posts into multiple languages using DeepL. Monthly characters limit on each plan.
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCancel ?></div></div>
                    <div class="feature-value"><div>100k chars/m</div></div>
                    <div class="feature-value"><div>300k chars/m</div></div>
                    <div class="feature-value"><div>1m chars/m</div></div>
                    <div class="feature-value"><div>5m chars/m</div></div>
                    <div class="feature-value"><div>15m chars/m</div></div>
                </div>

                <div class="feature-title">
                    Developer
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Data API
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Access public data of your blog via API
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Console API
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                The same API we use in our Console
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Delivery API
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                For self-serving a blog within Web Frameworks.
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Webhooks
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Receive an HTTP request on events in your blog
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

                <div class="feature-title">
                    Integrations
                </div>

                <div class="feature">
                    <div class="feature-name">
                        Hyvor Talk Comments
                        <span class="info-tooltip">
                            <?= $svgInfo ?>
                            <span class="tooltip">
                                Add Hyvor Talk commenting system for FREE
                            </span>
                        </span>
                    </div>
                    <div class="feature-value"><div><?= $svgCancel ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                    <div class="feature-value"><div><?= $svgCheck ?></div></div>
                </div>

            </div>

        </div>

    </div>

    <div class="button-main" style="margin-top: 100px;">
        @include('landing.inc.signup-button', ['buttonName' => 'Sign Up Now'])
    </div>

</div>


</div>

<script>

var currentSectionStart = 0;

function enableSections(startFrom) {
    currentSectionStart = startFrom;
    const max = window.innerWidth < 900 ? 1 : 3;
    console.log(window.innerWidth, max)
    document.querySelectorAll('.pricing-table-new .plan').forEach((plan, i) => {
        if (i < startFrom || i - startFrom >= max) {
            plan.classList.add('hidden');
        } else {
            plan.classList.remove('hidden');
        }
    })

    document.querySelectorAll('.pricing-table-new .feature').forEach(feature => {
        feature.querySelectorAll('.feature-value').forEach((value, i) => {
            if (i < startFrom || i - startFrom >= max) {
                value.classList.add('hidden');
            } else {
                value.classList.remove('hidden');
            }
        })
    })

    if (startFrom === 3) {
        document.querySelector('.more-plans-right-button').classList.add('hidden');
    } else {
        document.querySelector('.more-plans-right-button').classList.remove('hidden');
    }

    if (startFrom === 0) {
        document.querySelector('.more-plans-left-button').classList.add('hidden');
    } else {
        document.querySelector('.more-plans-left-button').classList.remove('hidden');
    }
}
enableSections(0);

function increaseSection() {
    enableSections(currentSectionStart + 1);
}

function decreaseSection() {
    enableSections(currentSectionStart - 1);
}

window.addEventListener('resize', () => {
    enableSections(currentSectionStart);
})

</script>


<div class="wave">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<div class="faqs">
    <div class="faq">
        <h3>Do you offer a trial?</h3>
        <p>Yes, we offer a 7-day trial with most of the features included. No credit card required to activate the trial.</p>
    </div>
    <div class="faq">
        <h3>Do I have to pay for themes?</h3>
        <p>No, all <a class="link" href="/themes">official themes</a> are free and open-source. You can easily install a theme on your blog from the Console.</p>
    </div>
    <div class="faq">
        <h3>Do you offer discounts?</h3>
        <p>
            You get 2-months off if you pay annually. In addition, we provide a 20% discount for non-profit organizations and early-stage startups. Contact us via live chat to get the coupon.
        </p>
    </div>
    <div class="faq">
        <h3>How do payments work?</h3>
        <p>
            Payments are processed securely through our Merchant of Record, <a href="https://paddle.com" rel="nofollow" class="link">Paddle</a>, who technically works as a reseller of the product. We support cards and Paypal in multiple currencies.
        </p>
    </div>
    <div class="faq">
        <h3>Can I cancel anytime?</h3>
        <p>
            Yes, absolutely. You can easily cancel your subscription from our Console - no questions asked. You can also <a href="/docs/export" class="link">export</a> your data anytime and move to another platform anytime you wish.
        </p>
    </div>
</div>

<div class="button-main">
    @include('landing.inc.signup-button', ['buttonName' => 'Sign Up Now'])
</div>

@include('landing.footer')

</body>
</html>