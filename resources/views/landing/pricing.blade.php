<?php

$svgCheck = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#896c6b" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg>';
$svgCancel = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ccc" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
</svg>';

$pricingRow = '<tr>
                <th></th>
                <th>Price</th>
                <th>Users</th>
                <th>Storage</th>
            </tr>';

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

<div class="pricing-table">

    <div class="container">

        <table>

            {!! $pricingRow !!}        

            <tr>
                <td>Plan A</td>
                <td><span class="price">$19</span>/month</td>
                <td>2</td>
                <td>40GB</td>
            </tr>

            <tr>
                <td>Plan B</td>
                <td><span class="price">$49</span>/month</td>
                <td>10</td>
                <td>250GB</td>
            </tr>

            <tr>
                <td>Plan C</td>
                <td><span class="price">$299</span>/month</td>
                <td>100</td>
                <td>1TB</td>
            </tr>

            <tr>
                <td>Plan D</td>
                <td><span class="price">$699</span>/month</td>
                <td>1000</td>
                <td>2TB</td>
            </tr>

            <tr>
                <td>Plan E</td>
                <td><span class="price">$1299</span>/month</td>
                <td>Unlimited</td>
                <td>5TB</td>
            </tr>


        </table>

    </div>
</div>


<div class="wave">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<div class="faqs">
    <div class="faq">
        <h3>How do pricing and plans work?</h3>
        <p>Each blog requires a separate subscription based on the number of <a class="link" href="/docs/users">users</a> and total media storage usage of that blog. There are no feature limitations - all features are included in all plans.</p>
    </div>
    <div class="faq">
        <h3>How do payments work?</h3>
        <p>
            Payments are processed securely through our Merchant of Record, <a href="https://paddle.com" rel="nofollow" class="link">Paddle</a>, who technically works as a reseller of the product.
        </p>
        <ul>
            <li><a target="_blank" href="https://www.paddle.com/help/start/intro-to-paddle/what-currencies-do-you-support" class="link" rel="nofollow">Supported Currencies</a></li>
            <li><a target="_blank" href="https://www.paddle.com/help/start/intro-to-paddle/which-payment-methods-do-you-support" class="link" rel="nofollow">Supported Payment Methods</a></li>
        </ul>
    </div>
    <div class="faq">
        <h3>Can I cancel anytime?</h3>
        <p>
            Yes, absolutely. You can easily cancel your subscription from our Console - no questions asked. You can also <a href="/docs/export" class="link">export</a> your data anytime and move to another platform anytime you wish. However, we do not provide refunds. We ask you to test our platform in the 14-days trial before subscribing.
        </p>
    </div>
</div>

<div class="button-main">
    <a data-flashload-skip-link href="/console?signup=1" class="button big">
        Start a Blog
    </a>
</div>

@include('landing.footer')

</body>
</html>