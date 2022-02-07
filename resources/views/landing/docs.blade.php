<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('landing.meta', [
        'title' => $title,
        'image' => '',
        'canonical' => "https://blogs.hyvor.com/docs/$pageName",
    ])
</head>
<body class="docs-page">

    @include('landing.nav')

    <div id="sidebar">
      
        @foreach ($nav as $sectionTitle => $pages)

            <div class="nav-section">
                
                <div class="nav-section-title">{{ $sectionTitle }}</div>

                <div class="nav-section-pages">
                    
                    @foreach ($pages as $page)
                        <a class="nav-page {{ ($page[0] ?? 'index' ) == $pageName ? 'active' : '' }}" href="/docs/{{$page[0]}}">
                            {{ $page[1] }}
                        </a>
                    @endforeach

                </div>

            </div>

        @endforeach

    </div>


    <div id="content-view">


        <content>
            {!! $content !!}
        </content>

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
    </script>

    <script src="/js-static/docs-prism.js"></script>

</body>
</html>