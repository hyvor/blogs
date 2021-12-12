<meta charset="utf-8">


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

<link rel="stylesheet" type="text/css" href="/css/landing.css">

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/instantclick/3.1.0/instantclick.min.js"></script>
<script>
    window.addEventListener('load', function() {
        InstantClick.init();
    })
</script>