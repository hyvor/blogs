<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('landing.meta', [
        'title' => 'Hyvor Blogs Themes',
        'canonical' => "https://blogs.hyvor.com/themes$route",
    ])
</head>
<body class="docs-page themes-page {{$currentTheme->name}}">

@include('landing.nav')

<div id="sidebar">

    @foreach ([$originalThemes, $portedThemes] as $themes)

        <div class="nav-section">
            <div class="nav-section-title">@if ($loop->index) Ported @else Original @endif</div>
            <div class="nav-section-pages">

                @foreach ($themes as $theme)

                    @if ($theme->name === 'blank')
                        @continue
                    @endif

                    <a
                        class="nav-page {{ ($theme->name ?? 'default' ) == $currentTheme->name ? 'active' : '' }}"
                        href="{{ $theme->name === 'default' ? '/themes' : "/themes/$theme->name" }}"
                    >
                        {{ $theme->name }}
                    </a>
                @endforeach

            </div>
        </div>

    @endforeach

</div>


<div id="content-view">

    @php
        $deliveryDomain = config('blogs.domain_delivery');
        $port = env('APP_ENV') === 'local' ? ':8081' : '';
        $url = "//{$currentTheme->versions[0]->preview_subdomain}.$deliveryDomain$port"
    @endphp

    <div class="navi">
        <div class="left">
            <a
                href="{{ $url }}"
                target="_blank"
            >Open in new tab &nbsp;<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                </svg></a>
        </div>
        <div class="right">
            <span class="desktop" onclick="changeMobile('desktop')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-laptop" viewBox="0 0 16 16">
  <path d="M13.5 3a.5.5 0 0 1 .5.5V11H2V3.5a.5.5 0 0 1 .5-.5h11zm-11-1A1.5 1.5 0 0 0 1 3.5V12h14V3.5A1.5 1.5 0 0 0 13.5 2h-11zM0 12.5h16a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 12.5z"/>
</svg></span>
            <span class="mobile" onclick="changeMobile('mobile')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-phone" viewBox="0 0 16 16">
  <path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
  <path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
</svg></span>
        </div>
    </div>


    <div class="iframe-wrap">
        <iframe src="{{ $url }}"></iframe>
    </div>

</div>

<script>
    // nav pos on scroll
    window.addEventListener('scroll', function() {
        var nav = document.getElementById("sidebar");
        nav.style.top = Math.max(65-window.scrollY, 15) + "px";
    });

    // scroll active
    var active = document.querySelector(".nav-page.active");
    active && !isInViewport(active) && active.scrollIntoView({
        block: 'center',
        inline: "nearest"
    });
    function isInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)

        );
    }


    var contentView = document.getElementById("content-view");
        function changeMobile(type) {
        if (type === 'desktop') {
            contentView.classList.remove("mobile");
        } else {
            contentView.classList.add("mobile");
        }
    }
</script>

</body>
</html>