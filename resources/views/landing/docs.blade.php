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
<body class="docs-page {{$pageName}}">

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

        function selfRemovingWindowEvent(on, func) {
            var remover = function() {
                window.removeEventListener(on, func);
                window.removeEventListener('flashload:navigationStarted', remover)
            }
            window.addEventListener(on, func);
            window.addEventListener('flashload:navigationStarted', remover)
        }

        function handleScroll() {
            var nav = document.getElementById("sidebar");
            nav.style.top = Math.max(65-window.scrollY, 15) + "px";
        }
        selfRemovingWindowEvent('scroll', handleScroll)

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

        // linkify headings
        var hs = document.querySelectorAll("h2[id],h3[id],h4[id]");
        for (var i = 0; i < hs.length; i++) {
            var h = hs[i];


            var icon = document.createElement("a");
            icon.className = "anchor-link";
            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/></svg>';
            h.appendChild(icon);

            var id = h.getAttribute('id');
            var link = document.createElement('a');
            link.setAttribute('href', '#' + id);
            link.innerHTML = h.innerHTML;
            h.innerHTML = link.outerHTML;
        }
    </script>

    <script src="/js-static/docs-prism.js"></script>

</body>
</html>