<?php

$blogs = [
    [
        'image' => 'hyvor-blog_evzdkh.webp',
        'url' => 'hyvor.com/blog'
    ],
    [
        'image' => 'moonshots-blog_vwlnpk.webp',
        'url' => 'blog.metamoonshots.io'
    ],
    [
        'image' => 'didgii-blog_yuhiqv.webp',
        'url' => 'blog.didgii.com'
    ],
    [
        'image' => 'deflekt_ik0u1p.webp',
        'url' => 'deflekt.ai/blog'
    ],
    [
        'image' => 'cyberhirn-blog_ff1go6.webp',
        'url' => 'cyberhirn.de'
    ],
    [
        'image' => 'supun-blog_hn24y3.webp',
        'url' => 'supun.io'
    ],
    [
        'image' => 'consevatruth-blog_kglswp.webp',
        'url' => 'www.conservatruth.com'
    ],
    [
        'image' => 'williu-blog_mlwazp.png',
        'url' => 'willliu.net'
    ]
];

?>
<!DOCTYPE html>
<html>
<head>
    @include('landing.meta', [
        'title' => 'Hyvor Blogs - Customers',
        'description' => 'Customers Spotlight of Hyvor Blogs',
        'canonical' => 'https://blogs.hyvor.com/customers',
    ])
</head>

<body class="customers">

@include('landing.nav')

<div class="customer-screenshots-wrap">

    <div class="container">

        <h1>
            Customer Spotlight
        </h1>

        <p>
            See how our customers use Hyvor Blogs. Possibilities are endless!
        </p>

        <div class="customers-inner">

            @foreach($blogs as $blog)

                <div class="customer">
                    <a href="//{{ $blog['url'] }}" target="_blank" rel="nofollow">
                        <img src="https://res.cloudinary.com/dqabfne6s/image/upload/c_scale,w_800/v1687716078/blogs.hyvor.com/customer-screenshots/{{ $blog['image'] }}" alt="{{ $blog['url'] }}">
                    </a>
                    <div class="name">
                        <a href="//{{ $blog['url'] }}" target="_blank" rel="nofollow">
                            {{ $blog['url'] }}
                        </a>
                    </div>
                </div>

            @endforeach

        </div>

    </div>

</div>


<div class="wave">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 250"><path fill="#fffaf8" fill-opacity="1" d="M0,128L60,138.7C120,149,240,171,360,170.7C480,171,600,149,720,149.3C840,149,960,171,1080,170.7C1200,171,1320,149,1380,138.7L1440,128L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path></svg></div>

<p style="width: 500px;max-width: 100%;margin: auto;">
    Hyvor Blogs can be customized to fit any website or brand. Are you ready to start your blog?
</p>

@include('landing.inc.signup-button', ['buttonName' => 'Start My Blog'])

@include('landing.footer')

</body>