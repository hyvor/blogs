@if (!\Illuminate\Support\Facades\App::environment('local', 'testing'))
    <script async src="https://cdn.splitbee.io/sb.js"></script>

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