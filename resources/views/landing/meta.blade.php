<meta charset="utf-8">

<?php

    $description = isset($description) ? $description : '';
    $canonical = isset($canonical) ? $canonical : '';
    $image = $image ?? '';

?>


<!-- SEO -->
<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>">

<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- OG -->
<meta property="og:type" content= "website" />
<meta property="og:url" content=" <?= $canonical ?> "/>
<meta property="og:site_name" content="Hyvor Blogs" />
<meta property="og:image" content="<?= $image ?>" />
<meta property="og:title" content="<?= $title ?>"/>
<meta property="og:description" content="<?= $description ?>"/>

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:domain" content="blogs.hyvor.com"/>
<meta name="twitter:title" content="<?= $title ?>" />
<meta name="twitter:description" content="<?= $description ?>" />
<meta name="twitter:image" content="<?= $image ?>" />
<link rel="canonical" href="<?= $canonical ?? '' ?>">

<link rel="shortcut icon" href="/favicon.ico">
<meta name="theme-color" content="#896c6b" />

@vite('resources/css/landing/landing.scss')

<script src="/js-static/flashload.js"></script>
<script data-flashload-skip-script>
    window.addEventListener('DOMContentLoaded', function() {
        Flashload.start({
            bar: true,
            barDelay: 100
        })
    });
</script>

@include('shared.tracking')