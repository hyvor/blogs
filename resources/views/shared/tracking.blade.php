@if (!\Illuminate\Support\Facades\App::environment('local', 'testing'))
    <script async src="https://cdn.splitbee.io/sb.js"></script>

    {{-- BING --}}
    {{--<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"97038522"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>--}}
    {{-- BING END --}}

    {{-- AFFILIATE --}}
    <script async src="https://cdn.tolt.io/tolt.js" data-tolt="3509c076-d24f-4cb2-b3ec-3ec0b54d186b"></script>
    {{-- AFFILIATE END --}}

    {{-- GOOGLE --}}{{--
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-10985628367"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-10985628367');
    </script>--}}
    {{-- GOOGLE END --}}

    {{-- POSTHOG --}}
    <script>
        !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capturing has_opted_out_capturing opt_in_capturing reset isFeatureEnabled onFeatureFlags getFeatureFlag getFeatureFlagPayload reloadFeatureFlags group updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures getActiveMatchingSurveys getSurveys".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
        posthog.init('phc_4Xu2CRcbyBGDK5n3onD4hvqKiCacZgwBoGNgH3O84df',{api_host:'https://eu.posthog.com'})
    </script>

@endif