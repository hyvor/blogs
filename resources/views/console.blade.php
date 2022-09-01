<!DOCTYPE html>
<html>
<head>
    <title>Console - Hyvor Blogs</title>
    <meta name="robots" content="nofollow, noindex">

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div id="app"></div>

    <script>
        var appConfig = <?php echo json_encode($config); ?>;
    </script>

    @viteReactRefresh
    @vite('resources/js/console/console.tsx')

    <script>
        function setUpPaddle() {
            @if (config('services.paddle.sandbox'))
                Paddle.Environment.set('sandbox');
            @endif
            Paddle.Setup({
                vendor: {{ config('services.paddle.vendor_id')  }}
            });
        }
    </script>
    <script async src="https://cdn.paddle.com/paddle/paddle.js" onload="setUpPaddle()"></script>

    {{-- CRISP --}}
    <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="1cab78fb-4baf-497e-a10f-00a3b12cfcfe";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>

</body>
</html>