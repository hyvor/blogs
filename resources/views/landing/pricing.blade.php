<?php

$svgCheck = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#896c6b" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg>';
$svgCancel = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ccc" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
</svg>';

$pricingRow = '<tr>
                <th></th>
                <th>
                    <div class="plan-name">Pro</div>
                    <div class="plan-price">
                        <div class="price">$8</div>
                        <div class="price-details">
                            <span class="period">per month</span>
                            <span class="period">billed annually</span>
                        </div>
                    </div>
                    <div class="plan-price-monthly">
                        <b>$10</b> billed monthly
                    </div>
                </th>
                <th>
                    <div class="plan-name">Team</div>
                    <div class="plan-price">
                        <div class="price">$10</div>
                        <div class="price-details">
                            <span class="period">per month / user</span>
                            <span class="period">billed annually</span>
                        </div>
                    </div>
                    <div class="plan-price-monthly">
                        <b>$16</b> billed monthly
                    </div>
                </th>
                <th>
                    <div class="plan-name">Enterprise</div>
                    <div class="plan-price">
                        <div class="price">$1000</div>
                        <div class="price-details">
                            <span class="period">per month</span>
                            <span class="period">billed annually</span>
                        </div>
                    </div>
                    <div class="plan-price-monthly">
                        <b>$1600</b> billed monthly
                    </div>
                </th>
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
                <td>Users</td>
                <td>2</td>
                <td>3 to 99</td>
                <td>Unlimited</td>
            </tr>

            <tr>
                <td>Media Storage</td>
                <td>10GB</td>
                <td>20GB per user</td>
                <td>2TB</td>
            </tr>

            <tr>
                <td>Media</td>
                <td>Images</td>
                <td>Images</td>
                <td>Any file type</td>
            </tr>


            <tr>
                <td>Code Injecting</td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
            </tr>


            <tr>
                <td>Custom Themes</td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
            </tr>

            <tr>
                <td>Custom Domain</td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
            </tr>

            <tr>
                <td>Multi-Language</td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
            </tr>

            <tr>
                <td>Webhooks</td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
            </tr>

            <tr>
                <td>API Access</td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCheck ?></td>
                <td><?= $svgCheck ?></td>
            </tr>

            <tr>
                <td>Edge Caching</td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCancel ?></td>
                <td>Coming Soon</td>
            </tr>

            <!-- <tr>
                <td>SAML Login</td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCheck ?></td>
            </tr>

            <tr>
                <td>Custom Console</td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCancel ?></td>
                <td><?= $svgCheck ?></td>
            </tr> -->


            {!! $pricingRow !!}  

        </table>

        <p>
            Each blog requires a seperate subscription.<br> The prices are shown <b>excluding</b> applicable VAT charges.
        </p>

    </div>
</div>


<div class="wave">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#f1e8e8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

@include('landing.footer')

</body>
</html>