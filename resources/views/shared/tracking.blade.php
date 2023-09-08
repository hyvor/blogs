@if (!\Illuminate\Support\Facades\App::environment('local', 'testing'))
    <script async src="https://cdn.splitbee.io/sb.js"></script>

    {{-- BING --}}
    {{--<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"97038522"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>--}}
    {{-- BING END --}}

    {{-- AFFILIATE --}}
    <script async src="https://cdn.tolt.io/tolt.js" data-tolt="3509c076-d24f-4cb2-b3ec-3ec0b54d186b"></script>
    {{-- AFFILIATE END --}}

    {{-- GOOGLE --}}
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-10985628367"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-10985628367');
    </script>
    {{-- GOOGLE END --}}

    {{-- PROFITWELL --}}
    <script id="profitwell-js" data-pw-auth="5b7888c594f1580aece249d322375e48">
        (function(i,s,o,g,r,a,m){i[o]=i[o]||function(){(i[o].q=i[o].q||[]).push(arguments)};
        a=s.createElement(g);m=s.getElementsByTagName(g)[0];a.async=1;a.src=r+'?auth='+
        s.getElementById(o+'-js').getAttribute('data-pw-auth');m.parentNode.insertBefore(a,m);
        })(window,document,'profitwell','script','https://public.profitwell.com/js/profitwell.js');

        profitwell('start', {});
    </script>
    {{-- PROFITWELL - END --}}

@endif